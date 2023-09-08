<?php

// Выводим отрендеренный шаблон в поток вывода
echo $this->twig_render('/layouts/layout.htm', ['contentLayout' => '404.htm', 'contentTitle' => '404 Page']);