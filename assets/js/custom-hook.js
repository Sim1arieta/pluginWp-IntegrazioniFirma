//Uso dei listener universali su id se con contactform7 ho problemi

document.addEventListener('clickXXX', function (e) {

    Swal.fire({
        title: 'Sto elaborando...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });


});

document.addEventListener('wpcf7mailsentXXX', (e) => {

    const { cart_url, checkout_url } = e.detail.apiResponse || {};

    // reindirizziamo alla prima URL disponibile
    if (cart_url) window.location.href = cart_url;
    else if (checkout_url) window.location.href = checkout_url;

    Swal.close();
});



// Timeout di sicurezza (1min)
// setTimeout(() => {
//     if (Swal.isVisible()) {
//         swalInstance.close();
//         Swal.fire('Attenzione', 'Il server sta impiegando più del previsto', 'info');
//     }
// }, 60000);