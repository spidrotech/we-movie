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
            let overview = data.overview.length < 5 ? "No detailed overview available." : data.overview;
            document.getElementById('movie-overview').textContent = overview;
            document.getElementById('movie-release-date').textContent = data.release_date || 'Unknown';
            document.getElementById('movie-rating').textContent = data.vote_average ? `${data.vote_average}/10` : 'No rating';
            document.getElementById('movie-genres').textContent = data.genres ? data.genres.map(genre => genre.name).join(', ') : 'N/A';
            document.getElementById('movie-id').value = data.id ? data.id : movieId;
            // Reset stars based on the fetched rating
            const userRating = data.rating || 0;
            document.querySelectorAll('.star').forEach(star => {
                star.style.color = (star.getAttribute('data-value') <= userRating) ? 'gold' : 'gray';
            });
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
        const overview = movie.overview.length > 100 ? movie.overview.slice(0, 100) + '...' : movie.overview;
        if (featuredMovieDiv) { // Ensure the featuredMovieDiv exists
            featuredMovieDiv.innerHTML = `
                <h3>${movie.title}</h3>
                <p>${overview}</p>
                <img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" alt="${movie.title}" />
            `;
        } else {
            console.warn('Featured movie section not found.');
        }
    }

    // Function to display movie cards
    function displayMovieCard(movie) {
        const overview = movie.overview.length > 100 ? movie.overview.slice(0, 100) + '...' : movie.overview;
        const movieCard = `
            <div class="col-md-2 mb-2">
                <div class="card">
                    <img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" class="card-img-top movie-poster" alt="${movie.title}">
                    <div class="movie-details">
                        <h5>${movie.title}</h5>
                        <p>${overview}</p>
                        <button class="btn btn-primary movie-button view-details" data-movie-id="${movie.id}">Lire le détails</button>
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
            });
    }

    // Attach event listeners for movie buttons (Lire le détails)
    function attachMovieButtonEvents() {
        moviesContainer.addEventListener('click', (event) => {
            const button = event.target.closest('.view-details');
            if (button) {
                const movieId = button.getAttribute('data-movie-id');
                showMovieDetails(movieId);
            }
        });
    }

    // Initial event listener setup
    genreItems.forEach(item => {
        item.addEventListener('click', () => {
            const genreId = item.getAttribute('data-genre-id');
            const genreName = item.getAttribute('data-genre-name');
            fetchMoviesByGenre(genreId, genreName);
        });
    });

    attachMovieButtonEvents(); // Attach events for the first time

    // Event listener for search input
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query.length > 2) { // Start searching at 3 characters
            fetch(`/search/autocomplete?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    moviesContainer.innerHTML = ''; // Clear old movie cards
                    bestMovieTrailerContainer.innerHTML = '';
                    genreTitle.innerHTML = 'Resultats:';
                    if (data.length === 0) {
                        moviesContainer.innerHTML = '<p>No movies found.</p>';
                    } else {
                        data.forEach(movie => displayMovieCard(movie));
                    }
                })
                .catch(error => {
                    console.error('Error fetching autocomplete results:', error);
                });
        } else {
            moviesContainer.innerHTML = ''; // Clear movie cards if query is empty
        }
    });

    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function () {
            const rating = this.getAttribute('data-value');
            const movieId = document.getElementById('movie-id').value; // Get movieId from hidden input or context
            fetch('/api/movie/rate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ movieId: movieId, rating: rating })
            })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    // Update the stars to reflect the new rating
                    document.querySelectorAll('.star').forEach(s => {
                        s.style.color = (s.getAttribute('data-value') <= rating) ? 'gold' : 'gray';
                    });
                });
        });
    });

});
