<?php

require 'vendor/autoload.php';

use Felprangel\BuscadorCursosAlura\Buscador;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$url ='https://www.alura.com.br/cursos-online-programacao/php';
$client = new Client();
$crawler = new Crawler();

$buscador = new Buscador($client, $crawler);
$cursos = $buscador->buscar($url);