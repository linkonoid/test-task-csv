<?php

// Выводим отрендеренный шаблон в поток вывода
echo $this->twig_render('/layouts/layout.htm', ['contentLayout' => 'upload.htm', 'contentTitle' => 'CSV Upload Page']);