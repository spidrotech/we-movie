<?php
// src/Controller/MovieController.php
namespace App\Controller;

use App\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    private MovieService $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $genres = $this->movieService->getGenres();
        $bestMovies = $this->movieService->getBestMovies();
    
        // Fetch the trailer for the first best movie
        $trailer = $this->movieService->getMovieTrailer($bestMovies[0]['id']);
    
        return $this->render('index.html.twig', [
            'genres' => $genres,
            'bestMovies' => $bestMovies,
            'url' => $trailer['url'], 
            'video_id' => $trailer['video_id'], 
        ]);
    }

    #[Route('/api/genres', name: 'api_genres', methods: ['GET'])]
    public function getGenres(): JsonResponse
    {
        $genres = $this->movieService->getGenres();
        return $this->json($genres);
    }

    #[Route('/api/movies/{genreId}', name: 'api_movies', methods: ['GET'])]
    public function getMoviesByGenre(int $genreId): JsonResponse
    {
        $movies = $this->movieService->getMoviesByGenre($genreId);
        return $this->json($movies);
    }

    #[Route('/api/movie/{movieId}', name: 'api_movie_details', methods: ['GET'])]
    public function getMovieDetails(int $movieId): JsonResponse
    {
        $movieDetails = $this->movieService->getMovieDetails($movieId);
        return $this->json($movieDetails);
    }

#[Route('/search/autocomplete', name: 'movie_autocomplete')]
public function autocomplete(Request $request): JsonResponse
{
    $query = $request->query->get('query');

    if ($query) {
        // Get movies matching the search query
        $movies = $this->movieService->searchMovies($query);

        // Return necessary fields for displaying movie cards
        return new JsonResponse(array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'] ?? null,
                'overview' => $movie['overview'] ?? '',
            ];
        }, $movies));
    }

    return new JsonResponse([]);
}
}
