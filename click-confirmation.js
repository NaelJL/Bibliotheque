const buttons = document.querySelectorAll('.button');

buttons.forEach(button => {

    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const bookId = this.dataset.bookId;
    
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'returned-book.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
    
                // Afficher un message de confirmation
                const confirmationMessage = document.getElementById('confirmation-message');
                confirmationMessage.innerHTML = 'Le livre a été retourné avec succès !';
                button.style.display = "none";
            } else {
                alert('Une erreur est survenue lors de la requête AJAX.');
            }
        };
        xhr.onerror = function() {
            alert('Une erreur est survenue lors de la requête AJAX.');
        };
        xhr.send('returned=' + bookId);
    });
});
