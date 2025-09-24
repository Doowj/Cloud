navbar = document.querySelector('.header .flex .navbar');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   profile.classList.remove('active');
}

profile = document.querySelector('.header .flex .profile');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   navbar.classList.remove('active');
}

window.onscroll = () =>{
   navbar.classList.remove('active');
   profile.classList.remove('active');
}

function loader(){
   document.querySelector('.loader').style.display = 'none';
}

function fadeOut(){
   setInterval(loader, 2000);
}

window.onload = fadeOut;

document.querySelectorAll('input[type="number"]').forEach(numberInput => {
   numberInput.oninput = () =>{
      if(numberInput.value.length > numberInput.maxLength) numberInput.value = numberInput.value.slice(0, numberInput.maxLength);
   };
});


let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
}

document.addEventListener('DOMContentLoaded', function() {
   var paymentMethodSelect = document.getElementById('payment-method');
   var creditCardInfo = document.getElementById('credit-card-info');
   var placeOrderBtn = document.getElementById('place-order-btn');

   paymentMethodSelect.addEventListener('change', function() {
      if (paymentMethodSelect.value === 'credit card') {
         creditCardInfo.style.display = 'block';
      } else {
         creditCardInfo.style.display = 'none';
      }
   });
});


$(() => {

   // Autofocus
   $('form :input:not(button):first').focus();
   $('.err:first').prev().focus();
   $('.err:first').prev().find(':input:first').focus();
   
   // Confirmation message
   $('[data-confirm]').on('click', e => {
       const text = e.target.dataset.confirm || 'Are you sure?';
       if (!confirm(text)) {
           e.preventDefault();
           e.stopImmediatePropagation();
       }
   });

   // Initiate GET request
   $('[data-get]').on('click', e => {
       e.preventDefault();
       const url = e.target.dataset.get;
       location = url || location;
   });

   // Initiate POST request
   $('[data-post]').on('click', e => {
       e.preventDefault();
       const url = e.target.dataset.post;
       const f = $('<form>').appendTo(document.body)[0];
       f.method = 'POST';
       f.action = url || location;
       f.submit();
   });

   // Reset form
   $('[type=reset]').on('click', e => {
       e.preventDefault();
       location = location;
   });

   // Auto uppercase
   $('[data-upper]').on('input', e => {
       const a = e.target.selectionStart;
       const b = e.target.selectionEnd;
       e.target.value = e.target.value.toUpperCase();
       e.target.setSelectionRange(a, b);
   });

   // Photo preview
   $('label.upload input[type=file]').on('change', e => {
       const f = e.target.files[0];
       const img = $(e.target).siblings('img')[0];

       if (!img) return;

       img.dataset.src ??= img.src;

       if (f?.type.startsWith('image/')) {
           img.src = URL.createObjectURL(f);
       }
       else {
           img.src = img.dataset.src;
           e.target.value = '';
       }
   });

});