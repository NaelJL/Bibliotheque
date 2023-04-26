const buttons = document.querySelectorAll('.button');
const forms = document.querySelectorAll('.form');

// Pour chaque bouton cliqué, faire apparaitre le formulaire et disparaitre le bouton
for (let i = 0; i < buttons.length; i++) {
  buttons[i].addEventListener('click', function() {
    const form = forms[i];
    if (form.style.display === 'none') {
      form.style.display = 'block';
      buttons[i].style.display = 'none';
    } else {
      form.style.display = 'none';
      buttons[i].style.display = 'block';
    }
  });
}