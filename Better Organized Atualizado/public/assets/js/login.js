const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

function showSignUp() {
	container.classList.add("right-panel-active");
}

function showSignIn() {
	container.classList.remove("right-panel-active");
}

signUpButton.addEventListener('click', showSignUp);
signInButton.addEventListener('click', showSignIn);

// Botões extras que só aparecem no layout mobile (empilhado)
document.querySelectorAll('[data-switch="sign-up"]').forEach(btn => btn.addEventListener('click', showSignUp));
document.querySelectorAll('[data-switch="sign-in"]').forEach(btn => btn.addEventListener('click', showSignIn));
