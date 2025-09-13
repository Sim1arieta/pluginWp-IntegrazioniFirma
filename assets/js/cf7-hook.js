// assets/js/cf7-hook.js (snippet)

/*
Eventi di wpcf7
wpcf7beforesubmit
wpcf7submit
wpcf7mailsent
wpcf7mailfailed
wpcf7invalid
wpcf7spam
wpcf7statuschanged
*/

document.addEventListener('wpcf7beforesubmit', function(e){
    
  Swal.fire({
    title:'Sto elaborando...', 
    allowOutsideClick:false, 
    didOpen:()=>Swal.showLoading()
  });
  
  
});

//TODO implementare acnhe con id form oltre con il listener cf7
document.addEventListener('wpcf7mailsent', (e) => {
  
  const { cart_url, checkout_url } = e.detail.apiResponse || {};

  // reindirizziamo alla prima URL disponibile
  // if (cart_url) window.location.href = cart_url;
  // else if (checkout_url) window.location.href = checkout_url;

  Swal.close();
});

['wpcf7mailfailed', 'wpcf7invalid', 'wpcf7spam'].forEach((evt) => {
  document.addEventListener(evt, () => Swal.close());
});