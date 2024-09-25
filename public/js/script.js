document.addEventListener("DOMContentLoaded", () =>{
    //enlarger image on click
    const image = document.querySelector(".main-area img");
    const largePhotoContainer = document.querySelector(".large-photo");
    const largePhoto = largePhotoContainer.querySelector("img");

    //check if image present
    if(image){
        image.addEventListener("click", ()=>{
            largePhotoContainer.classList.add("visible");
            largePhoto.src = image.src;
        });
    }

    //on photo click, close it
    largePhotoContainer.addEventListener("click", e =>{
       e.currentTarget.classList.remove("visible");
    });

    //on scroll, close it
    window.addEventListener("scroll", () =>{
        largePhotoContainer.classList.remove("visible");
    })
});
