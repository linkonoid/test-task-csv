<?php

/**
 * @author Max Barulin (https://github.com/linkonoid)
 */

 header('Content-Type: text/plain; charset=utf-8');

// Обрабатываем csv-файл

try {
    
    if (
        !isset($_FILES['upfile']['error']) ||
        is_array($_FILES['upfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded form filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    //Прописываем ограничения на загрузку - размер и расширения файла
    define('KB', 1024);
    define('MB', 1048576);
    define('GB', 1073741824);
    define('TB', 1099511627776);
    if ($_FILES['upfile']['size'] > 5*MB) {
        echo $_FILES['upfile']['size'];
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['upfile']['tmp_name']),
        array(
            'csv' => 'text/csv',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    $file = sprintf('./storage/uploads/%s.%s',sha1_file($_FILES['upfile']['tmp_name']),$ext);

    if (!move_uploaded_file($_FILES['upfile']['tmp_name'],$file)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    //Проверяем таблицы на наличие, если нет - создаём и чистим временную таблицу для загрузки данных
    $this->db->createTables();
	$this->db->cleanPriceTmp(); 

    //Загружаем данные во временную таблицу и приводим данные к нормальному состоянию
    $fields = $this->config['db']['models']['price']['fields'];
    $keys = array_keys($fields);

    $csv =  array_map(
        fn($item) => str_getcsv(mb_convert_encoding(trim(
            str_replace(
                        ["\\", "\""],
                        [""],
                        $item
                    )
        ), 'UTF8'), ';'),
        file($file)
    );

    array_shift($csv);

    foreach ($csv as $record)
    {
        $data = [];

        //По условию задачи нужна модель данных(ну пусть будет...), создаём динамически из данных в файле конфигурации 
        $model = new stdClass();

        foreach ($record as $key => $value)
        {
            $tmp_value = $value;

            if (isset($keys[$key])) {
                
                $field = $keys[$key];

                $model->{$field} = null;

                switch ($fields[$field]['type']) {
                    case 'string':
                        $tmp_value = htmlspecialchars($tmp_value, ENT_QUOTES, "UTF-8");
                        $tmp_value = mb_substr($tmp_value, 0 , $fields[$field]['length']);
                        break;
                    case 'bool':
                        $tmp_value = str_replace(" ","", $tmp_value);
                        $tmp_value = str_replace("", 0, $tmp_value);
                        $tmp_value = (bool) $tmp_value ? 1 : 0;
                        break;
                    case 'decimal':
                        $tmp_value = str_replace(['"',"'"," "],"", (string) $tmp_value);
                        $tmp_value = str_replace([','],".",$tmp_value);
                        $tmp_value = (double) rtrim(sprintf('%0.'. $fields[$field]['length'] .'f', $tmp_value), '0');
                        break;
                    case 'integer':
                        $tmp_value = (int) sprintf('%d', $tmp_value);
                        break;
                    default: throw new TypeError("Cannot type: " .$fields[$field]['type']);
                }
            }

            $model->{$field} = $tmp_value;
        }
        //var_dump($model);
        $this->db->insert('price_tmp', $model);
    }

    //Мержим временные данные в постоянную таблицу
    $this->db->merge('price', 'price_tmp');

    //Редирект на price page
    header("Location: /price");
    exit;

} catch (RuntimeException $e) {

    echo $e->getMessage();

}