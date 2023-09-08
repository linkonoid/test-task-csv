<?php

// Выводим отрендеренный шаблон в поток вывода
echo $this->twig_render('/layouts/layout.htm', ['contentLayout' => 'index.htm', 'contentTitle' => 'Index Page']);
