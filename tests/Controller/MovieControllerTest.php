<?php 

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MovieControllerTest extends WebTestCase
{
    public function testGetGenres()
    {
        $client = static::createClient();

        $client->request('GET', '/api/genres');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetMoviesByGenre()
    {
        $client = static::createClient();

        $genreId = 878; // Example genre ID
        $client->request('GET', "/api/movies/{$genreId}");
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        dd($data); 
        $this->assertIsArray($data);
    }
    // TODO: tests for other endpoints...
}

