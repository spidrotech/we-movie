<?php 
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Rating;
use App\Repository\RatingRepository;
use Symfony\Component\HttpFoundation\Request;

class MovieService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $language;
    private EntityManagerInterface $entityManager;
    private RatingRepository $ratingRepository;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager, RatingRepository $ratingRepository, string $apiKey)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->ratingRepository = $ratingRepository;
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
        $movies = array_slice($response->toArray()['results'], 0, 10); // Return top 10 movies
        return $this->addRatingsToMovies($movies);
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
        $movies = $response->toArray()['results'];
        return $this->addRatingsToMovies($movies);
    }

    public function getMovieDetails(int $movieId): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$this->apiKey}&language={$this->language}");
        $movie = $response->toArray();
        return $this->addRatingToMovie($movie);
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
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/search/movie?api_key={$this->apiKey}&language={$this->language}", [
            'query' => [
                'query' => $query
            ]
        ]);
    
        $data = $response->toArray();
        return $this->addRatingsToMovies($data['results']);
    }

    public function rateMovie(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        $movieId = $data['movieId'] ?? null;
        $ratingValue = $data['rating'] ?? null;

        if (!$movieId || $ratingValue < 1 || $ratingValue > 5) {
            return ['error' => 'Invalid input'];
        }

        $rating = (new Rating())
            ->setMovieId($movieId)
            ->setRating($ratingValue);

        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        return ['message' => 'Rating saved'];
    }
    
    private function addRatingsToMovies(array $movies): array
    {
        foreach ($movies as &$movie) {
            $movie = $this->addRatingToMovie($movie);
        }
        return $movies;
    }

    private function addRatingToMovie(array $movie): array
    {
        $rating = $this->ratingRepository->findRatingByMovieId($movie['id']);
        
        if ($rating) {
            $movie['rating'] = $rating['rating'];
        } else {
            $movie['rating'] = null;
        }
        return $movie;
    }

}
