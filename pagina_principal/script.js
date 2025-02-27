
document.addEventListener("DOMContentLoaded", function () {
    const botonFiltros = document.getElementById("toggleFilters");
    const botonCerrarFiltros= document.getElementById("closeFilters");
    const filterContainer = document.getElementById("filterContainer");

    // Mostrar el contenedor de filtros con animación
    botonFiltros.addEventListener("click", function () {
        filterContainer.style.display = "block";
        filterContainer.style.position = "fixed";
        filterContainer.style.top = "0";
        filterContainer.style.left = "-300px";
        filterContainer.style.width = "300px";
        filterContainer.style.height = "100%";
        filterContainer.style.background = "white";
        filterContainer.style.boxShadow = "2px 0 5px rgba(0, 0, 0, 0.5)";
        filterContainer.style.overflowY = "auto";
        filterContainer.style.padding = "20px";
        filterContainer.style.zIndex = "1050";
        filterContainer.style.transition = "left 0.4s ease";

        // Muestra el filtro
        setTimeout(() => {
            filterContainer.style.left = "0";
        }, 10);
    });

    // Ocultar el filtro
    botonCerrarFiltros.addEventListener("click", function () {
        filterContainer.style.left = "-300px";

        // Espera a que termine la animación antes de ocultarlo
        setTimeout(() => {
            filterContainer.style.display = "none";
        }, 400); 
    });
});
