// assets/js/cf7-hook.js (snippet)
document.addEventListener('wpcf7submit', function(e){
  Swal.fire({title:'Elaboro...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
  // redirect gestito da PHP verso ifData.cartUrl
  
});
