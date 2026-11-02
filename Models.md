# Documentación de modelos (app/Models)

Tienda e-commerce multi-tienda

## 1. Store (`stores`)

**Para qué sirve:** representa una tienda de la plataforma. Guarda su nombre, logo, moneda e impuesto.
**Por qué existe:** es la base del diseño multi-tienda. Casi todo lo demás pertenece a una tienda.

| Dato clave | Detalle                                                                           |
| ---------- | --------------------------------------------------------------------------------- |
| Columnas   | `name`, `logo`, `currency` (3 letras, ej. `COP`), `tax_rate` (porcentaje)         |
| Relaciones | tiene muchos `User`, `Category`, `Product`, `ShippingMethod`, `Coupon`, `Order`   |
| Nota       | La moneda y el impuesto se configuran por tienda desde el panel de administración |

## 2. User (`users`)

**Para qué sirve:** cuentas de acceso: clientes, administradores de tienda y super administrador.
**Por qué existe:** es el modelo de autenticación que trae Laravel; se extendió con `store_id`, `role` y `phone`.

| Dato clave         | Detalle                                                                                              |
| ------------------ | ---------------------------------------------------------------------------------------------------- |
| Columnas agregadas | `store_id` (nullable), `role`, `phone`                                                               |
| Roles              | `customer`, `admin`, `super_admin`                                                                   |
| Relaciones         | pertenece a `Store`; tiene muchas `Address`, `Order`                                                 |
| Nota               | El `super_admin` es el único con `store_id = NULL`, porque gestiona las tiendas y no pertenece a una |

## 3. Address (`addresses`)

**Para qué sirve:** direcciones de envío de un cliente.
**Por qué existe:** un cliente puede tener varias direcciones y elegir una al comprar.

| Dato clave | Detalle                                                                                                                                                   |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Columnas   | `user_id`, `recipient_name`, `phone`, `address_line`, `city`, `state`, `postal_code`, `country`, `is_default`                                             |
| Relaciones | pertenece a `User`; puede estar en muchos `Order`                                                                                                         |
| Nota       | No lleva `store_id`: llega a la tienda a través del usuario. Los pedidos guardan una copia de la dirección, así que editarla no altera pedidos anteriores |

## 4. Category (`categories`)

**Para qué sirve:** clasifica los productos del catálogo (filtros y navegación).
**Por qué existe:** el cliente filtra por categoría y el administrador organiza su catálogo.

| Dato clave | Detalle                                                                                                                                                                         |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Columnas   | `store_id`, `name`, `slug`, `is_active`                                                                                                                                         |
| Relaciones | pertenece a `Store`; tiene muchos `Product`                                                                                                                                     |
| Nota       | `slug` es único **por tienda** (`store_id` + `slug`), no global, para que dos tiendas puedan tener la misma categoría. No se puede borrar una categoría que aún tenga productos |

## 5. Product (`products`)

**Para qué sirve:** el catálogo de venta: nombre, descripción, precio y stock.
**Por qué existe:** es el centro de la tienda; lo que se muestra, se compra y se inventaría.

| Dato clave | Detalle                                                                                                                                                                    |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Columnas   | `store_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `min_stock`, `is_active`                                                                       |
| Relaciones | pertenece a `Store` y `Category`; tiene muchos `ProductImage`, `OrderItem`, `InventoryMovement`                                                                            |
| Nota       | `min_stock` dispara la alerta de stock bajo. `is_active` permite desactivar sin borrar, para no romper pedidos antiguos. `slug` es único por tienda (URLs amigables y SEO) |

## 6. ProductImage (`product_images`)

**Para qué sirve:** las imágenes de un producto.
**Por qué existe:** un producto necesita varias imágenes (galería), algo que no cabe en una sola columna de `products`.

| Dato clave | Detalle                                                                                     |
| ---------- | ------------------------------------------------------------------------------------------- |
| Columnas   | `product_id`, `path`, `alt_text`, `sort_order`, `is_primary`                                |
| Relaciones | pertenece a `Product`                                                                       |
| Nota       | `path` guarda la ruta del archivo, no la imagen. `alt_text` ayuda al SEO y la accesibilidad |

## 7. ShippingMethod (`shipping_methods`)

**Para qué sirve:** opciones de envío y su costo.
**Por qué existe:** cada tienda define cómo envía y cuánto cobra; el cliente elige una en el checkout.

| Dato clave | Detalle                                                |
| ---------- | ------------------------------------------------------ |
| Columnas   | `store_id`, `name`, `description`, `cost`, `is_active` |
| Relaciones | pertenece a `Store`; usado por muchos `Order`          |

## 8. Coupon (`coupons`)

**Para qué sirve:** códigos de descuento (fase posterior del proyecto).
**Por qué existe:** permite promociones por porcentaje o valor fijo, con límites de uso y vigencia.

| Dato clave | Detalle                                                                                                                                            |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Columnas   | `store_id`, `code`, `type` (`percentage` / `fixed`), `value`, `min_order_amount`, `max_uses`, `used_count`, `starts_at`, `expires_at`, `is_active` |
| Relaciones | pertenece a `Store`; aplicado en muchos `Order`                                                                                                    |
| Nota       | `code` es único por tienda. `max_uses = NULL` significa usos ilimitados                                                                            |

## 9. Order (`orders`)

**Para qué sirve:** la cabecera de cada compra: quién compró, totales, estado y envío.
**Por qué existe:** es el registro de la venta y alimenta el historial del cliente, la gestión de pedidos y los reportes.

| Dato clave | Detalle                                                                                                                                                                                                                                                            |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Columnas   | `store_id`, `user_id`, `address_id`, `shipping_method_id`, `coupon_id`, `order_number`, `status`, `subtotal`, `discount`, `tax`, `shipping_cost`, `total`, `shipping_address_snapshot`, `notes`                                                                    |
| Estados    | `pending`, `paid`, `processing`, `shipped`, `delivered`, `cancelled`                                                                                                                                                                                               |
| Relaciones | pertenece a `Store`, `User`, `Address`, `ShippingMethod`, `Coupon`; tiene muchos `OrderItem`, `Payment`, `InventoryMovement`                                                                                                                                       |
| Nota       | Los totales se guardan tal como se cobraron y no se recalculan. `user_id` no tiene borrado en cascada: no se puede borrar un usuario con pedidos. Crear el pedido, descontar stock y registrar el movimiento debe hacerse en **una sola transacción** con rollback |

## 10. OrderItem (`order_items`)

**Para qué sirve:** el detalle del pedido: cada producto comprado y su cantidad.
**Por qué existe:** un pedido tiene varios productos; se separa de `orders` para modelar la relación uno a muchos.

| Dato clave | Detalle                                                                                                                                         |
| ---------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| Columnas   | `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `total`                                                                     |
| Relaciones | pertenece a `Order` y `Product`                                                                                                                 |
| Nota       | `product_name` y `unit_price` son copias del momento de la compra: si el precio cambia después, los pedidos viejos conservan el precio original |

## 11. Payment (`payments`)

**Para qué sirve:** intentos y confirmaciones de pago de un pedido.
**Por qué existe:** un pedido puede tener varios intentos (uno rechazado, luego uno aprobado) y se necesita trazabilidad con la pasarela de pagos.

| Dato clave | Detalle                                                                                                 |
| ---------- | ------------------------------------------------------------------------------------------------------- |
| Columnas   | `order_id`, `method`, `status`, `amount`, `transaction_reference`, `gateway_response` (JSON), `paid_at` |
| Estados    | `pending`, `approved`, `rejected`, `refunded`                                                           |
| Relaciones | pertenece a `Order`                                                                                     |
| Nota       | `gateway_response` guarda la respuesta completa de la pasarela, útil para depurar                       |

## 12. InventoryMovement (`inventory_movements`)

**Para qué sirve:** historial de cada entrada, salida o ajuste de stock.
**Por qué existe:** el stock actual vive en `products.stock`, pero se necesita saber **por qué y quién** lo cambió (auditoría y reportes de rotación).

| Dato clave | Detalle                                                                                                        |
| ---------- | -------------------------------------------------------------------------------------------------------------- |
| Columnas   | `product_id`, `user_id`, `order_id`, `type` (`in` / `out` / `adjustment`), `quantity`, `stock_after`, `reason` |
| Relaciones | pertenece a `Product`; opcionalmente a `User` (responsable) y `Order` (si fue por una venta)                   |
| Nota       | `quantity` admite negativos (ajustes). `stock_after` guarda el stock resultante tras el movimiento             |

## 13. AuditLog (`audit_logs`)

**Para qué sirve:** registro de acciones críticas (cambiar un precio, cancelar un pedido, etc.).
**Por qué existe:** requisito de auditoría: poder saber quién hizo qué, cuándo y con qué valores antes y después.

| Dato clave | Detalle                                                                                                       |
| ---------- | ------------------------------------------------------------------------------------------------------------- |
| Columnas   | `user_id`, `action`, `auditable_type`, `auditable_id`, `old_values` (JSON), `new_values` (JSON), `ip_address` |
| Relaciones | pertenece a `User`; relación polimórfica (`morphTo`) con cualquier modelo auditado                            |
| Nota       | La relación polimórfica permite auditar cualquier tabla sin una llave foránea fija                            |

---

## Mapa de relaciones (resumen)

```
Store ─┬─ User ─┬─ Address
       │        └─ Order ─┬─ OrderItem ── Product
       ├─ Category ── Product ─┬─ ProductImage
       │                       └─ InventoryMovement
       ├─ ShippingMethod ── Order
       ├─ Coupon ── Order
       └─ Order ── Payment
AuditLog ── User (y cualquier modelo auditado)
```
