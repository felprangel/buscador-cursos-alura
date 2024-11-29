<?php

use Felprangel\BuscadorCursosAlura\Buscador;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

beforeEach(function () {
    $this->httpClient = $this->createMock(ClientInterface::class);
    $this->crawler = $this->createMock(Crawler::class);
    $this->buscador = new Buscador($this->httpClient, $this->crawler);
});

it('deve retornar cursos encontrados na página', function () {
    $url = 'https://www.alura.com.br/cursos-online';
    $html = '<html><body><span class="card-curso__nome">Curso PHP</span><span class="card-curso__nome">Curso Laravel</span></body></html>';

    $respostaMock = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $respostaMock->method('getBody')->willReturn($html);
    $this->httpClient->method('request')->with('GET', $url)->willReturn($respostaMock);

    $crawlerMock = $this->createMock(Crawler::class);
    $crawlerMock->method('filter')->with('span.card-curso__nome')->willReturn(new Crawler($html));

    $buscador = new Buscador($this->httpClient, $crawlerMock);

    $cursos = $buscador->buscar($url);

    expect($cursos)->toEqual(['Curso PHP', 'Curso Laravel']);
});

it('deve retornar um array vazio quando não encontrar cursos', function () {
    $url = 'https://www.alura.com.br/cursos-online';
    $html = '<html><body></body></html>'; // Nenhum curso na página

    $respostaMock = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $respostaMock->method('getBody')->willReturn($html);
    $this->httpClient->method('request')->with('GET', $url)->willReturn($respostaMock);

    $crawlerMock = $this->createMock(Crawler::class);
    $crawlerMock->method('filter')->with('span.card-curso__nome')->willReturn(new Crawler($html));

    $buscador = new Buscador($this->httpClient, $crawlerMock);

    $cursos = $buscador->buscar($url);

    expect($cursos)->toEqual([]);
});

it('deve lançar exceção caso a requisição falhe', function () {
    $url = 'https://www.alura.com.br/cursos-online';

    $this->httpClient->method('request')->willThrowException(new \Exception('Erro na requisição'));

    $buscador = new Buscador($this->httpClient, $this->crawler);

    expect(fn () => $buscador->buscar($url))->toThrow(\Exception::class);
});
