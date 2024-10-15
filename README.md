# We Movies

We Movies is a web application that allows users to explore movies by genre, view details, and search for movies. 
The application utilizes The Movie Database (TMDb) API to fetch movie data and provides a user-friendly interface for movie enthusiasts.

## Features

- Browse movies by genre.
- View detailed information about each movie, including title, overview, release date, rating, and genres.
- Search for movies with autocomplete suggestions.
- Display top-rated movies.
- Design using Bootstrap.

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Backend**: PHP, Symfony
- **Database**: MySQL
- **API**: The Movie Database (TMDb) API
- **Development**: Docker

## Installation

### Prerequisites

- PHP 8.3 or higher
- Symfony 6.4
- Mysql 8.0
- Composer
- Docker
- npm/yarn
- git

### Steps to Install

1. Clone the repository:
   ```bash
   git clone https://github.com/spidrotech/we-movie.git
   ```
2. Setup: 
   ```bash
   cd we-movie
   docker compose up --build -d
   yarn encore dev
   ```
3. Access URL: 
http://localhost:8000 

## Troubleshooting

For any issues or troubleshooting, please contact: amrihafedh@yahoo.fr