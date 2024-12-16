const open_modal = document.querySelector('.open-modal');
const modal = document.querySelector('.modal');
const close_modal = document.querySelector('.close-modal');
const stts = document.getElementsByTagName('td.stts');
console.log(stts)

open_modal.addEventListener('click', () => {

    modal.setAttribute('open', '');

    close_modal.addEventListener('click', () => {
        modal.removeAttribute('open');
    })
})

