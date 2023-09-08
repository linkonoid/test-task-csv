<?php

/**
 * @author Max Barulin (https://github.com/linkonoid)
 */

// Выводим отрендеренный шаблон в поток вывода
$fields = $this->config['db']['models']['price']['fields'];
$data = $this->db->select('price');
echo $this->twig_render('/layouts/layout.htm', ['contentLayout' => 'price.htm', 'contentTitle' => 'Price Page', 'fields' => $fields, 'data' => $data]);