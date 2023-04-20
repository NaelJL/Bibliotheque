// regular expressions pour les pages signin, login, modif-data (admin et user), share-a-book, modif-book (admin et user)
const patterns = {
    'name': /^[a-z]{2,15}$/i,
    'surname': /^[a-z]{2,15}$/i,
    // \w : lettres majuscules, minuscules, chiffres et _
    'email': /^([\w\.-]+)@([a-z\d]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/i,
    'password': /^[\w@&$]{5,10}$/i,
    'password2': /^[\w@&$]{5,10}$/i,
    // acceptent les espaces, apostrophes et :
    'title': /^[a-z ':\d]{2,30}$/i,
    'author': /^[a-z ':\d]{2,25}$/i,
    'translator': /^[a-z ':\d]{2,25}$/i,
    'collection': /^[a-z ':\d]{2,25}$/i,
    'edition': /^[a-z ':\d]{2,25}$/i,
}

// récupérer l'input
const inputs = document.querySelectorAll('input');

// fonction pour changer la couleur de la border en cas d'erreur ou de réussite
function validate(field, regex){
    if(regex.test(field.value)){
        field.className = 'valid';
    } else {
        field.className = 'invalid';
    }
}

// event listener sur les inputs
inputs.forEach((input) => {
    input.addEventListener('keyup', (e) => {
        validate(e.target, patterns[e.target.attributes.name.value]);
    })
})
