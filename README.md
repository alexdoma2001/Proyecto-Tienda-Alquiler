# Proyecto-Tienda-Alquiler
Se trata de una tienda de alquiler de videojuegos realizada con PHP, JavaScript, HTML5, CSS3, Bootstrap y phpMyAdmin.
Aquí explicaremos brevemente el funcionamiento de la aplicación, pero en el documento: "memoria Tienda Alquiler" se detallarán y explicarán todas las funcionalidades que se mencionan.
Esta tienda cuenta con una sección de administrador y una seccion de usuario.
En cuanto a la seccion de administrador nos encontraremos 5 secciones:
  1. Gestionar alquileres: Aquí se podrán filtrar los alquileres realizados por los clientes, haciendo busquedas de los pedidos tanto por el dni, tanto como el id del pedido. También se podrán eliminar alquileres de usuarios especificos y ordenar los alquileres en funcion de su estado (pendiente o finalizado)
  2. Gestionar Videojuegos: Aqui aparecen todo el listado de los videojuegos de la aplicación. En esta seccion se puede agregar nuevos videojuegos a traves de un formulario, editar los videojuegos existentes (pudiendo cambiar entre las categorias y plataformas con su respectivo precio al que pertenecen), controlar las unidades de un videojuego especifico en cada plataforma a la que pertenecen, y eliminar los videojuegos existentes.
  3. Gestionar Puntos de recogida: Aquí se podrán agregar nuevos puntos de recogida a traves de un formulario. Además, aparece un formulario que está en disable, donde se muestran los puntos de recogida que tenemos almacenados en nuestra bdd. Tendremos 2 botones en este formulario por cada punto de recogida que tengamos: editar y eliminar.
  4. Gestionar Plataformas: Aquí aparece un listado de las plataformas que tenemos en nuestra base de datos. Se podrá editar el nombre de la plataforma existente, eliminarla, o agregar nuevas plataformas, asociandolas a sus videojuegos, incluyendo un precio.
  5. Gestionar Categorias: Aquí aparece un listado de las categorías que tenemos en nuestra base de datos. Se podrá editar el nombre de la plataforma existente, eliminarla, o agregar nuevas plataformas, asociandolas a sus videojuegos

En cuanto a la seccion de usuario, nos encontramos las siguientes funcionalidades:
  1. Registro
  2. Login
  3. Añadir productos al carrito, siendo el carrito un array que se iniciará como una variable de sesión donde irá metiendo la clave de los productos.
  4. cambiar contraseña: a traves de un formulario que valida que la contraseña actual del usuario que inserta es valida, y la nueva que quiere meter coincide para validar que no mete una contraseña incorrecta.
  5. agregar saldo: a traves de un formulario de validacion de caracteres
  6. eliminar cuenta (si el usuario tiene alquileres pendientes por pagar, no podrá eliminar la cuenta)
  7.  ver alquileres pasados
  8.  ver alquileres activos, en donde si selecciona un alquiler activo, le aparecerán todos los datos del alquiler, y si se ha pasado el plazo, el valor de la multa generada por haber excedido los dias, el valor de el alquiler general, y una barra donde insertar un codigo de referencia para poder finalizar el alquiler (Hace de simulador para que en tiendas físicas se le de ese codigo para que el cliente finalice el alquiler)
  9.  filtrar videojuegos por categorias, plataformas u ambas.
  10.  realizar un alquiler

A su vez, la tienda cuenta con funcionalidades extra como envio de correos electronicos con el resumen del alquiler al cliente a la hora tanto de realizarlo, como de finalizarlo. Verificación de stock, saldo (que un usuario no pueda finalizar un alquiler si no tiene suficiente saldo) o busqueda de productos.
