<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    public function testGetBooks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetBook(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books/1');

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('Content-Type', 'application/json');
            $this->assertJson($client->getResponse()->getContent());
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateBook(): void
    {
        $client = static::createClient();
        $client->request('POST', '/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'publicationYear' => 2022,
            'isbn' => '9781234567897'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testUpdateBook(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/books/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Updated Book',
            'author' => 'Updated Author',
            'publicationYear' => 2022,
            'isbn' => '9781234567897'
        ]));

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('Content-Type', 'application/json');
            $this->assertJson($client->getResponse()->getContent());
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/books/1');

        if ($client->getResponse()->getStatusCode() === 204) {
            $this->assertResponseStatusCodeSame(204);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }
}
