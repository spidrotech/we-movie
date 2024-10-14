<?php

namespace App\Controller;

use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
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

        // Fetch the trailer for the first best movie if available
        $trailer = !empty($bestMovies) ? $this->movieService->getMovieTrailer($bestMovies[0]['id']) : null;

        return $this->render('index.html.twig', [
            'genres' => $genres,
            'bestMovies' => $bestMovies,
            'url' => $trailer['url'] ?? null,
            'video_id' => $trailer['video_id'] ?? null,
        ]);
    }

    #[Route('/api/genres', name: 'api_genres', methods: ['GET'])]
    public function getGenres(): JsonResponse
    {
        return $this->json($this->movieService->getGenres());
    }

    #[Route('/api/movies/{genreId}', name: 'api_movies', methods: ['GET'])]
    public function getMoviesByGenre(int $genreId): JsonResponse
    {
        return $this->json($this->movieService->getMoviesByGenre($genreId));
    }

    #[Route('/api/movie/{movieId}', name: 'api_movie_details', methods: ['GET'])]
    public function getMovieDetails(int $movieId): JsonResponse
    {
        return $this->json($this->movieService->getMovieDetails($movieId));
    }

    #[Route('/search/autocomplete', name: 'movie_autocomplete')]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('query', '');

        if ($query !== '') {
            $movies = $this->movieService->searchMovies($query);
            $moviesResponse = array_map(fn($movie) => [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'] ?? null,
                'overview' => $movie['overview'] ?? '',
            ], $movies);

            return new JsonResponse($moviesResponse);
        }
 
        return new JsonResponse([]);
    }

    #[Route('/api/movie/rate', name: 'rate_movie', methods: ['POST'])]
    public function rateMovie(Request $request): Response
    {
        $result = $this->movieService->rateMovie($request);

        if (isset($result['error'])) {
            return new JsonResponse($result, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

}
