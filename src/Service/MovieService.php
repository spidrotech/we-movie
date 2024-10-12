<?php 
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $language;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey; // TODO: remove from .env
        $this->language = 'fr-FR'; // TODO: get lang from browser
    }

    public function getGenres(): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/genre/movie/list?api_key={$this->apiKey}&language={$this->language}");
        return $response->toArray()['genres'];
    }
    public function getBestMovies(): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/top_rated?api_key={$this->apiKey}&language={$this->language}");
        return array_slice($response->toArray()['results'], 0, 9); // Return top 10 movies
    }
    
    // depends on whats needed! getBestMovie OR getBestMovies
    public function getBestMovie(): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/top_rated?api_key={$this->apiKey}&language={$this->language}");
        return $response->toArray()['results'][0]; // Get the top-rated movie.
    }

    public function getMoviesByGenre(int $genreId): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/discover/movie?api_key={$this->apiKey}&with_genres={$genreId}&language={$this->language}");
        return $response->toArray()['results'];
    }

    public function getMovieDetails(int $movieId): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$this->apiKey}&language={$this->language}");
        return $response->toArray();
    }

    public function getMovieTrailer(int $movieId): ?array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}/videos?api_key={$this->apiKey}");
        $videos = $response->toArray()['results'];
    
        foreach ($videos as $video) {
            if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                return [
                    'url' => "https://www.youtube.com/embed/" . $video['key'],
                    'video_id' => $video['key'],
                ];
            }
        }
    
        return null;
    }

    public function searchMovies(string $query): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query
            ]
        ]);
    
        $data = $response->toArray();
    
        // Return the movies with necessary fields
        return $data['results'];
    }
    
}
