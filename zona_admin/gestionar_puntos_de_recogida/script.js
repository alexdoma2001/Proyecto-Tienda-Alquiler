function editarPunto(id) {
    // Habilitar campos de texto en la fila especificada
    document.getElementById('nombre_' + id).disabled = false;
    document.getElementById('direccion_' + id).disabled = false;
    document.getElementById('ciudad_' + id).disabled = false;
    document.getElementById('codigo_postal_' + id).disabled = false;

    // Cambiar el botón de "Editar" a "Confirmar cambio"
    document.getElementById('editar_' + id).style.display = 'none';
    document.getElementById('confirmar_' + id).style.display = 'inline';
}

function confirmarCambio(id) {
    // Obtener valores de los campos de la fila
    const nombre = document.getElementById('nombre_' + id).value;
    const direccion = document.getElementById('direccion_' + id).value;
    const ciudad = document.getElementById('ciudad_' + id).value;
    const codigo_postal = document.getElementById('codigo_postal_' + id).value;

    // Redirigir a editar_punto_recogida.php con los valores actualizados
    window.location.href = `editar_punto_recogida.php?ID=${id}&nombre=${encodeURIComponent(nombre)}&direccion=${encodeURIComponent(direccion)}&ciudad=${encodeURIComponent(ciudad)}&codigo_postal=${encodeURIComponent(codigo_postal)}`;
}