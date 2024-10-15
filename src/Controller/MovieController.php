<?php

namespace App\Controller;

use App\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    private MovieService $movieService;
    private SerializerInterface $serializer;

    public function __construct(MovieService $movieService, SerializerInterface $serializer)
    {
        $this->movieService = $movieService;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $genres = $this->movieService->getGenres();
        $bestMovies = $this->movieService->getBestMovies();
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
        $data = $this->movieService->getGenres();
        $json = $this->serializer->serialize($data, 'json');
        return JsonResponse::fromJsonString($json);
    }

    #[Route('/api/movies/{genreId}', name: 'api_movies', methods: ['GET'])]
    public function getMoviesByGenre(int $genreId): JsonResponse
    {
        $data = $this->movieService->getMoviesByGenre($genreId);
        $json = $this->serializer->serialize($data, 'json');
        return JsonResponse::fromJsonString($json);
    }

    #[Route('/api/movie/{movieId}', name: 'api_movie_details', methods: ['GET'])]
    public function getMovieDetails(int $movieId): JsonResponse
    {
        $data = $this->movieService->getMovieDetails($movieId);
        $json = $this->serializer->serialize($data, 'json');
        return JsonResponse::fromJsonString($json);
    }

    #[Route('/api/movie/search/autocomplete', name: 'movie_autocomplete')]
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
            $json = $this->serializer->serialize($moviesResponse, 'json');
            return JsonResponse::fromJsonString($json);
        }

        return new JsonResponse([]);
    }

    #[Route('/api/movie/rate', name: 'rate_movie', methods: ['POST'])]
    public function rateMovie(Request $request): JsonResponse
    {
        $result = $this->movieService->rateMovie($request);
        $statusCode = isset($result['error']) ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK;
        $json = $this->serializer->serialize($result, 'json');
        return JsonResponse::fromJsonString($json, $statusCode);
    }
}
