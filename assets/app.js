// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Add the functions to the window object for global access
window.showMovieDetails = function (movieId) {
    fetch(`/api/movie/${movieId}`)
        .then(response => response.json())
        .then(data => {
            // Populate modal with movie details
            document.getElementById('movie-title').textContent = data.title;
            document.getElementById('movie-poster').src = `https://image.tmdb.org/t/p/w500${data.poster_path}`;

            // Handle overview with a minimum description length
            let overview = data.overview.length < 50 ? "No detailed overview available." : data.overview;
            document.getElementById('movie-overview').textContent = overview;
            document.getElementById('movie-release-date').textContent = data.release_date || 'Unknown';
            document.getElementById('movie-rating').textContent = data.vote_average ? `${data.vote_average}/10` : 'No rating';
            document.getElementById('movie-genres').textContent = data.genres ? data.genres.map(genre => genre.name).join(', ') : 'N/A';

            // Show the modal
            const movieDetailsModal = new bootstrap.Modal(document.getElementById('movieDetailsModal'));
            movieDetailsModal.show();
        });
};

document.addEventListener('DOMContentLoaded', () => {
    const genreItems = document.querySelectorAll('.genre-item');
    const featuredMovieDiv = document.getElementById('featured-movie');
    const moviesContainer = document.getElementById('movies-container');
    const genreTitle = document.getElementById('genre-title');
    const searchInput = document.getElementById('movie-search');
    const bestMovieTrailerContainer = document.getElementById('bestMovieTrailer');

    
    // Check if featuredMovieDiv exists before using it
    function displayFeaturedMovie(movie) {
        if (featuredMovieDiv) { // Ensure the featuredMovieDiv exists
            featuredMovieDiv.innerHTML = `
                <h3>${movie.title}</h3>
                <p>${movie.overview}</p>
                <img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" alt="${movie.title}" />
            `;
        } else {
            console.warn('Featured movie section not found.');
        }
    }

    // Function to display movie cards
    function displayMovieCard(movie) {
        const movieCard = `
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" class="card-img-top movie-poster" alt="${movie.title}">
                    <div class="movie-details">
                        <h5>${movie.title}</h5>
                        <p>${movie.overview}</p>
                        <button class="btn btn-primary movie-button view-details" data-movie-id="${movie.id}">View Details</button>
                    </div>
                </div>
            </div>`;
        moviesContainer.insertAdjacentHTML('beforeend', movieCard);
    }

    // Fetch movies by genre
    function fetchMoviesByGenre(genreId, genreName) {
        fetch(`/api/movies/${genreId}`)
            .then(response => response.json())
            .then(movies => {
                genreTitle.textContent = genreName;
                moviesContainer.innerHTML = ''; // Clear previous movies
                bestMovieTrailerContainer.innerHTML = '';
                movies.forEach(displayMovieCard);
                if (movies.length > 0) {
                    displayFeaturedMovie(movies[0]); // Show the first movie as featured
                }
                attachMovieButtonEvents(); // Attach events to new movie buttons
            });
    }

    // Attach event listeners for movie buttons (view details)
    function attachMovieButtonEvents() {
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', () => {
                const movieId = button.getAttribute('data-movie-id');
                showMovieDetails(movieId);
            });
        });
    }

    // Delegate the click event to the parent container
    moviesContainer.addEventListener('click', (event) => {
        const button = event.target.closest('.view-details');
        if (button) {
            const movieId = button.getAttribute('data-movie-id');
            showMovieDetails(movieId);
        }
    });

    // Attach other event listeners, such as for genre items
    genreItems.forEach(item => {
        item.addEventListener('click', () => {
            const genreId = item.getAttribute('data-genre-id');
            const genreName = item.getAttribute('data-genre-name');
            fetchMoviesByGenre(genreId, genreName);
        });
    });
    // Event listeners for genre items
    genreItems.forEach(item => {
        item.addEventListener('click', () => {
            const genreId = item.getAttribute('data-genre-id');
            const genreName = item.getAttribute('data-genre-name');
            fetchMoviesByGenre(genreId, genreName);
        });
    });

    // Event listener for search input
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();

        if (query.length > 2) { // Start searching after 2 characters
            fetch(`/search/autocomplete?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    moviesContainer.innerHTML = ''; // Clear old movie cards
                    bestMovieTrailerContainer.innerHTML = '';

                    if (data.length === 0) {
                        moviesContainer.innerHTML = '<p>No movies found.</p>';
                    } else {
                        data.forEach(movie => displayMovieCard(movie));
                        attachMovieButtonEvents(); // Attach events to new movie buttons
                    }
                })
                .catch(error => {
                    console.error('Error fetching autocomplete results:', error);
                });
        } else {
            moviesContainer.innerHTML = ''; // Clear movie cards if query is empty
        }
    });
});
