const searchForm = document.querySelector('.search-form');
const searchInput = document.querySelector('.search-input');
const searchResults = document.querySelector('.search-results');
const userPage = document.querySelector('.user-page');
const adminPage = document.querySelector('.admin-page');


// déclencher la fonction de recherche quand la touche est relevée
searchForm.addEventListener('keyup', function(e) {
    e.preventDefault();
    const searchQuery = searchInput.value.trim();

    // sortir si la recherche est vide
    if (searchQuery === "") {
        searchResults.innerHTML = "";
        return;
    }

    // savoir si la requête vient de la page admin ou user
    let page = "";
    if (adminPage) {
        page = "admin-page";
    } else if (userPage) {
        page = "user-page";
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'search.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            searchResults.innerHTML = this.responseText;
        }
    };
    xhr.send(`search=${searchQuery}&page=${page}`);
});