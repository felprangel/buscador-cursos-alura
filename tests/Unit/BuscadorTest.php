<?php

use Felprangel\BuscadorCursosAlura\Buscador;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

beforeEach(function () {
    // Mock de ClientInterface
    $this->httpClient = $this->createMock(ClientInterface::class);

    // Mock de Crawler
    $this->crawler = $this->createMock(Crawler::class);

    // Criando instância da classe Buscador
    $this->buscador = new Buscador($this->httpClient, $this->crawler);
});

it('deve retornar cursos encontrados na página', function () {
    // Dados fictícios para o teste
    $url = 'https://www.alura.com.br/cursos-online';
    $html = '<html><body><span class="card-curso__nome">Curso PHP</span><span class="card-curso__nome">Curso Laravel</span></body></html>';

    // Mock da resposta do HTTP
    $respostaMock = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $respostaMock->method('getBody')->willReturn($html);
    $this->httpClient->method('request')->with('GET', $url)->willReturn($respostaMock);

    // Mock da função filter do Crawler
    $crawlerMock = $this->createMock(Crawler::class);
    $crawlerMock->method('filter')->with('span.card-curso__nome')->willReturn(new Crawler($html));

    // Criando a instância do Buscador com o mock do Crawler
    $buscador = new Buscador($this->httpClient, $crawlerMock);

    // Chamada do método que queremos testar
    $cursos = $buscador->buscar($url);

    // Assertiva para verificar se os cursos foram extraídos corretamente
    expect($cursos)->toEqual(['Curso PHP', 'Curso Laravel']);
});

it('deve retornar um array vazio quando não encontrar cursos', function () {
    $url = 'https://www.alura.com.br/cursos-online';
    $html = '<html><body></body></html>'; // Nenhum curso na página

    // Mock da resposta HTTP
    $respostaMock = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
    $respostaMock->method('getBody')->willReturn($html);
    $this->httpClient->method('request')->with('GET', $url)->willReturn($respostaMock);

    // Mock da função filter do Crawler
    $crawlerMock = $this->createMock(Crawler::class);
    $crawlerMock->method('filter')->with('span.card-curso__nome')->willReturn(new Crawler($html));

    // Criando a instância do Buscador com o mock do Crawler
    $buscador = new Buscador($this->httpClient, $crawlerMock);

    // Chamada do método que queremos testar
    $cursos = $buscador->buscar($url);

    // Assertiva para verificar que o retorno é um array vazio
    expect($cursos)->toEqual([]);
});

it('deve lançar exceção caso a requisição falhe', function () {
    $url = 'https://www.alura.com.br/cursos-online';

    // Mock da resposta HTTP que lança uma exceção
    $this->httpClient->method('request')->willThrowException(new \Exception('Erro na requisição'));

    // Criando a instância do Buscador com o mock do Crawler
    $buscador = new Buscador($this->httpClient, $this->crawler);

    // Esperando que uma exceção seja lançada
    expect(fn () => $buscador->buscar($url))->toThrow(\Exception::class);
});
