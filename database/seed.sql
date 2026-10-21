-- =====================================================================
--  MUNDO ILUSIÓN · Datos REALES del catálogo (ejecutar DESPUÉS de schema.sql)
--  Fuente: "Catálogo Desayunos - Mundo Ilusión" (20 productos + 7 adicionales).
--  Usuarios de prueba (cámbialos antes de publicar en internet):
--    administrador@mundoilusion.test  (Admin123*)
--    empleado@mundoilusion.test       (Empleado123*)
--    cliente@mundoilusion.test        (Cliente123*)
-- =====================================================================
USE mundo_ilusion;
SET NAMES utf8mb4;

INSERT INTO usuario (documento,nombre,correo,telefono,password_hash,rol,fecha_aceptacion_politica) VALUES
 ('1000000001','Administrador Mundo Ilusión','administrador@mundoilusion.test','3000000001','$2y$10$G6/Zj0iZvSPztO8XWI.J2uTbrYmD/WWOHH85sOAn19JBp1wtAicFK','administrador',NOW()),
 ('1000000002','Empleado Demo','empleado@mundoilusion.test','3000000002','$2y$10$bQwo6PTx0QORq5SgJcAt6OfkcQtYM44HCCOduZz6CSbqCOF077S8S','empleado',NULL),
 ('1000000003','Cliente Demo','cliente@mundoilusion.test','3000000003','$2y$10$Kto5zJJmkNyCsOl4fhyT0.OfRQd9bqDvcdZalUl3frVwwd/E8o6d.','cliente',NOW());
INSERT INTO direccion (id_usuario,direccion,barrio,referencia) VALUES (3,'Calle 30 # 10-25, Apto 301','Bocagrande','Edificio frente al parque');
INSERT INTO categoria (nombre,descripcion,tipo) VALUES
 ('Desayunos sorpresa','Caja de madera con lazos, globos y un desayuno completo para sorprender','categoria'),
 ('Bandejas sorpresa','Bandejas decoradas con globo, tarjeta y un menú para compartir','categoria'),
 ('Cajitas de cartón','Cajitas decoradas con tu temática y un menú listo para disfrutar','categoria'),
 ('Adicionales','Extras para agregar a cualquier detalle','adicional');
INSERT INTO producto (nombre,descripcion,precio,disponible) VALUES
 ('Dulzuras de amor','Diseño:
- Caja de madera y lazos decorados según tu temática
- 1 o 2 globos lisos
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich o waffles
- 1 Jugo de naranja natural
- 1 manzana
- 1 Galleta o chocolatina
Adicionales disponibles:
- Globo burbuja o metalizado: +$5.000',55000,1),
 ('Cariños de amor','Diseño:
- Caja de madera y lazos decorados según tu temática
- 1 globo burbuja o metalizado + globo pequeño liso (opcional)
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich o arepa de chocolo
- 1 Jugo de naranja natural
- 1 mix de frutas
- 1 Bon yourt
Adicionales disponibles:
- 2 rosas eternas: +$10.000',65000,1),
 ('Destellos de ternura','Diseño:
- Caja de madera y lazos decorados según tu temática
- 1 globo burbuja o metalizado + globo pequeño liso (opcional)
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 mix de frutas
- 1 Te de Hatsu
- 1 galleta o chocolatina',73000,1),
 ('Rayito de Sol','Diseño:
- Caja de madera y lazos decorados según tu temática
- Globo burbuja y/o globo liso
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 mix de frutas
- 1 Cerveza coronita
- 1 frasco de frutos secos
Adicionales disponibles:
- 2 cervezas coronitas: +$10.000',77000,1),
 ('Toque mágico','Diseño:
- Caja de madera y lazos decorados según tu temática
- 1 globo burbuja o metalizado + globo pequeño liso (opcional)
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 mix de frutas
- 1 Te de Hatsu
- 1 frasco de frutos secos',85000,1),
 ('Pedacito de Cielo','Diseño:
- Caja tipo canasta de madera y lazos decorados según tu temática
- Arco de globos y/o globo metalizado y/o burbuja
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Te de Hatsu
- 1 frasco de frutos secos
Adicionales disponibles:
- 2 rosas eternas: +$10.000',92000,1),
 ('Amor Bonito','Diseño:
- Caja de madera y lazos decorados según tu temática
- Mini arco de globos
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Te de Hatsu
- 1 frasco de frutos secos
Adicionales disponibles:
- 2 rosas eternas: +$10.000',97000,1),
 ('Despertar Feliz','Diseño:
- Caja de madera y lazos decorados según tu temática
- Arco de globos y/o globo burbuja
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, waffles, arepa de chocolo o tamal
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Porción de postre o bon yourt
- 1 frasco de frutos secos
- 1 Te de hatsu o capuchino
Adicionales disponibles:
- 1 cerveza coronita: +$5.000
- 3 rosas eternas: +$12.000',112000,1),
 ('Corazón Contento','Diseño:
- Caja de madera y lazos decorados según tu temática
- Arco de globos y/o globo burbuja
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, arepa de chocolo, tamal o arepitas con huevos rancheros o rellenas de pollo
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Piazza
- 1 frasco de frutos secos
- 1 Galleta o chocolatina
- 1 Te de hatsu',145000,1),
 ('Toque Especial','Diseño:
- Caja de madera y lazos decorados según tu temática
- Arco de globos y/o globo burbuja y/o globo metalizado
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, arepa de chocolo, tamal o arepitas con huevos rancheros o rellenas de pollo
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Piazza
- 1 frasco de frutos secos
- 1 Galleta o chocolatina
- 1 Te de hatsu
- 1 Mini torta
- 1 Bon yourt o avena
Adicionales disponibles:
- 3 globos en helio: +$15.000',175000,1),
 ('Momento Inolvidable','Diseño:
- Caja de madera y lazos decorados según tu temática
- Arco de globos y/o globo burbuja
- 1 Peluche de 26 cm (aplica solo para referencias seleccionadas)
- Tarjeta de dedicatoria y/o foto (opcional)
Menú:
- 1 Sándwich, arepa de chocolo, tamal o arepitas con huevos rancheros o rellenas de pollo
- 1 Jugo de naranja natural
- 1 parfait de frutas con granola y yogurt griego
- 1 Piazza
- 1 frasco de frutos secos
- 1 Chocolate (M&M, MilkyWay, Hershey o similar)
- 1 Cerveza coronita o capuchino
Adicionales disponibles:
- 4 rosas eternas: +$18.000
Nota:
- Puedes cambiar el peluche por el gato negro.',200000,1),
 ('Dulzura Premium','Diseño:
- Caja de madera y lazos decorados según tu temática
- Arco de globos y/o globo burbuja
- 4 rosas eternas o naturales (según disponibilidad)
- Tarjeta de dedicatoria y/o foto (opcional)
- 3 globos en helio
Menú:
- 1 Sándwich, arepa de chocolo, tamal o arepitas con huevos rancheros o rellenas de pollo
- 1 Jugo de naranja natural
- 1 capuchino o milo
- 1 parfait de frutas con granola y yogurt griego
- 1 frasco de frutos secos
- 1 Chocolate (M&M, MilkyWay, Hershey o similar)
- 1 Mini torta
- 1 Gato negro o similar',270000,1),
 ('Bandeja de amor','Diseño:
- Bandeja circular decorada según tu temática
- Globo burbuja o metalizado
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Rollitos de tortillas de jamón y queso
- Mix de frutas
- Arepitas con salchichas
- Jugo de naranja',50000,1),
 ('Cariñitos en Bandeja','Diseño:
- Bandeja rectangular decorada según tu temática
- Globo burbuja o metalizado
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Sandwich
- Mix de frutas
- Galletas Minichips
- Empanaditas o deditos
- Centro: gomitas o galleticas
- Jugo de naranja',60000,1),
 ('Bandeja de amor Plus','Diseño:
- Bandeja circular decorada según tu temática
- Globo burbuja o metalizado
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Rollitos de tortillas de jamón y queso
- Mix de frutas
- Sandwich
- Arepitas con salchicha
- Centro: gomitas, salsa o galleticas
- Jugo de naranja',65000,1),
 ('Amor en Bandeja','Diseño:
- Bandeja rectangular decorada según tu temática
- Globo burbuja o metalizado
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Sandwich
- Mix de frutas
- Arepitas de queso
- Huevos rancheros
- Centro: gomitas o galleticas
- Jugo de naranja',70000,1),
 ('Un amanecer contigo','Diseño:
- Cajita de cartón decorada según tu temática
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Sandwich
- Mix de frutas
- Jugo de naranja
- Chocolatina o galleta
- Cerveza Coronita o milo',65000,1),
 ('Buffet Dulzura','Diseño:
- Cajita de cartón decorada según tu temática
- 2 globos en helio
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Sandwich
- Manzana
- Chocolatina
- Jugo de naranja
- Galleta Tosh o Club Social',68000,1),
 ('Buffet Tentación','Diseño:
- Cajita de cartón decorada según tu temática
- 2 globos en helio
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Sandwich
- Mini waffles
- Dedito de queso
- Galletas Mini Chips
- Salsa de la casa
- Jugo de naranja',73000,1),
 ('Aromas del amanecer','Diseño:
- Cajita de cartón decorada según tu temática
- 3 rosas eternas o naturales (según disponibilidad)
- Tarjeta de dedicatoria y foto (opcional)
Menú:
- Empanadas o deditos
- Jugo de naranja
- Chocolatina',66000,1),
 ('Globo burbuja o metalizado','Un globo burbuja o metalizado adicional para tu detalle.',5000,1),
 ('1 cerveza Coronita','Una cerveza Coronita adicional. Solo mayores de 18 años.',5000,1),
 ('2 rosas eternas','Dos rosas eternas para decorar tu detalle.',10000,1),
 ('2 cervezas Coronita','Dos cervezas Coronita adicionales. Solo mayores de 18 años.',10000,1),
 ('3 rosas eternas','Tres rosas eternas para decorar tu detalle.',12000,1),
 ('3 globos en helio','Tres globos en helio adicionales.',15000,1),
 ('4 rosas eternas','Cuatro rosas eternas para decorar tu detalle.',18000,1);
INSERT INTO producto_imagen (id_producto,ruta,orden,es_principal) VALUES
 (1,'img/productos/dulzuras-de-amor-1.jpg',0,1),
 (1,'img/productos/dulzuras-de-amor-2.jpg',1,0),
 (1,'img/productos/dulzuras-de-amor-3.jpg',2,0),
 (2,'img/productos/carinos-de-amor-1.jpg',0,1),
 (2,'img/productos/carinos-de-amor-2.jpg',1,0),
 (2,'img/productos/carinos-de-amor-3.jpg',2,0),
 (3,'img/productos/destellos-de-ternura-1.jpg',0,1),
 (3,'img/productos/destellos-de-ternura-2.jpg',1,0),
 (3,'img/productos/destellos-de-ternura-3.jpg',2,0),
 (4,'img/productos/rayito-de-sol-1.jpg',0,1),
 (4,'img/productos/rayito-de-sol-2.jpg',1,0),
 (4,'img/productos/rayito-de-sol-3.jpg',2,0),
 (5,'img/productos/toque-magico-1.jpg',0,1),
 (5,'img/productos/toque-magico-2.jpg',1,0),
 (5,'img/productos/toque-magico-3.jpg',2,0),
 (6,'img/productos/pedacito-de-cielo-1.jpg',0,1),
 (6,'img/productos/pedacito-de-cielo-2.jpg',1,0),
 (7,'img/productos/amor-bonito-1.jpg',0,1),
 (7,'img/productos/amor-bonito-2.jpg',1,0),
 (7,'img/productos/amor-bonito-3.jpg',2,0),
 (8,'img/productos/despertar-feliz-1.jpg',0,1),
 (8,'img/productos/despertar-feliz-2.jpg',1,0),
 (8,'img/productos/despertar-feliz-3.jpg',2,0),
 (9,'img/productos/corazon-contento-1.jpg',0,1),
 (9,'img/productos/corazon-contento-2.jpg',1,0),
 (9,'img/productos/corazon-contento-3.jpg',2,0),
 (10,'img/productos/toque-especial-1.jpg',0,1),
 (10,'img/productos/toque-especial-2.jpg',1,0),
 (10,'img/productos/toque-especial-3.jpg',2,0),
 (11,'img/productos/momento-inolvidable-1.jpg',0,1),
 (11,'img/productos/momento-inolvidable-2.jpg',1,0),
 (11,'img/productos/momento-inolvidable-3.jpg',2,0),
 (12,'img/productos/dulzura-premium-1.jpg',0,1),
 (12,'img/productos/dulzura-premium-2.jpg',1,0),
 (13,'img/productos/bandeja-de-amor-1.jpg',0,1),
 (14,'img/productos/carinitos-en-bandeja-1.jpg',0,1),
 (15,'img/productos/bandeja-de-amor-plus-1.jpg',0,1),
 (15,'img/productos/bandeja-de-amor-plus-2.jpg',1,0),
 (16,'img/productos/amor-en-bandeja-1.jpg',0,1),
 (17,'img/productos/un-amanecer-contigo-1.jpg',0,1),
 (17,'img/productos/un-amanecer-contigo-2.jpg',1,0),
 (17,'img/productos/un-amanecer-contigo-3.jpg',2,0),
 (18,'img/productos/buffet-dulzura-1.jpg',0,1),
 (18,'img/productos/buffet-dulzura-2.jpg',1,0),
 (19,'img/productos/buffet-tentacion-1.jpg',0,1),
 (19,'img/productos/buffet-tentacion-2.jpg',1,0),
 (20,'img/productos/aromas-del-amanecer-1.jpg',0,1),
 (21,'img/productos/extra-globo.svg',0,1),
 (22,'img/productos/extra-cerveza.svg',0,1),
 (23,'img/productos/extra-rosa.svg',0,1),
 (24,'img/productos/extra-cerveza.svg',0,1),
 (25,'img/productos/extra-rosa.svg',0,1),
 (26,'img/productos/extra-helio.svg',0,1),
 (27,'img/productos/extra-rosa.svg',0,1);
INSERT INTO producto_categoria (id_producto,id_categoria) VALUES
 (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,2),(14,2),(15,2),(16,2),(17,3),(18,3),(19,3),(20,3),(21,4),(22,4),(23,4),(24,4),(25,4),(26,4),(27,4);
INSERT INTO configuracion (clave,valor,descripcion) VALUES
 ('anticipacion_horas','24','Horas mínimas de anticipación para la fecha de entrega o recogida'),
 ('expiracion_pedido_horas','24','Horas que un pedido puede estar sin pagar antes de cancelarse automáticamente'),
 ('tienda_nombre','Mundo Ilusión','Nombre comercial'),
 ('tienda_slogan','Tienda de regalos','Frase corta bajo el logo'),
 ('tienda_mensaje','Cada detalle que creamos está realizado con mucho amor y propósito, para convertir tus momentos en inolvidables.','Mensaje de bienvenida (portada y página de la tienda)'),
 ('tienda_nota_personalizacion','Todos nuestros detalles son personalizados. Puedes modificar o agregar productos; el precio variará según disponibilidad.','Nota que aparece en cada producto'),
 ('tienda_direccion','Cartagena de Indias, Bolívar','Ubicación'),
 ('tienda_horarios','','Horarios de atención (ej. Lunes a sábado de 8:00 a. m. a 6:00 p. m.)'),
 ('tienda_telefono','','Teléfono de contacto'),
 ('tienda_whatsapp','','WhatsApp (10 dígitos, ej. 3001234567). Si lo llenas aparece el botón flotante de WhatsApp'),
 ('tienda_instagram','','Instagram (ej. @mundoilusion)'),
 ('tienda_facebook','','Facebook'),
 ('correo_negocio','','Correo que recibe el aviso de pedidos pagados'),
 ('aviso_licor','Los productos que incluyen licor (cerveza) se venden únicamente a mayores de 18 años.','Aviso para productos con licor'),
 ('politica_entrega','Las entregas a domicilio se programan según la fecha elegida. Debes estar disponible en la dirección indicada. Si nadie recibe el pedido, nos comunicaremos contigo para reprogramar.','Política de entrega'),
 ('politica_cambios_devoluciones','Por tratarse de productos personalizados y perecederos, solo se aceptan cambios o devoluciones por defectos de fabricación o daños en el transporte, reportados dentro de las 24 horas siguientes a la entrega.','Política de cambios y devoluciones'),
 ('politica_datos','Tratamos tus datos personales únicamente para gestionar tus pedidos y atenderte, conforme a la Ley 1581 de 2012. Puedes consultarlos, actualizarlos o solicitar su eliminación en cualquier momento.','Política de tratamiento de datos');
