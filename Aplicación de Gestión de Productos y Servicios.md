# Aplicación de Gestión de Productos y Servicios

## Descripción
Esta es una aplicación web desarrollada en PHP con MySQL que permite gestionar productos y servicios con fechas de inicio y fin, calculando automáticamente las diferencias de tiempo entre las fechas.

## Características Principales

### ✅ CRUD Completo
- **Crear**: Agregar nuevos productos/servicios con validación de fechas
- **Leer**: Visualizar listado completo con paginación y búsqueda
- **Actualizar**: Editar productos/servicios existentes
- **Eliminar**: Eliminar productos/servicios con confirmación

### ✅ Cálculo de Diferencias de Fechas
- Cálculo automático de diferencias en años, meses y días
- Visualización detallada de tiempo transcurrido
- Cálculo en tiempo real en formularios

### ✅ Funcionalidades Adicionales
- **Búsqueda**: Buscar por nombre, descripción o tipo
- **Filtrado**: Filtrar por tipo (producto/servicio)
- **Validaciones**: Validación de fechas y campos requeridos
- **Estados**: Indicador de estado (Pendiente, En curso, Finalizado)
- **Interfaz Responsiva**: Compatible con dispositivos móviles

## Estructura de Archivos

```
productos_app/
├── config.php              # Configuración de base de datos
├── Producto.php            # Clase modelo para operaciones CRUD
├── index.php               # Página principal con listado
├── create.php              # Formulario para crear productos
├── edit.php                # Formulario para editar productos
├── view.php                # Página de detalles del producto
├── delete.php              # Página de confirmación de eliminación
├── styles.css              # Estilos CSS personalizados
├── database.sql            # Script SQL original (con triggers)
├── database_simple.sql     # Script SQL simplificado
└── README.md               # Esta documentación
```

## Instalación y Configuración

### Requisitos
- PHP 8.1 o superior
- MySQL 8.0 o superior
- Apache 2.4 o superior
- Extensión PHP MySQL (php-mysql)

### Pasos de Instalación

1. **Clonar o copiar archivos**
   ```bash
   cp -r productos_app /var/www/html/
   ```

2. **Configurar base de datos**
   ```sql
   CREATE DATABASE productos_app;
   CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'app_password';
   GRANT ALL PRIVILEGES ON productos_app.* TO 'app_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Importar estructura de base de datos**
   ```bash
   mysql -u app_user -papp_password productos_app < database_simple.sql
   ```

4. **Configurar permisos**
   ```bash
   chown -R www-data:www-data /var/www/html/productos_app
   chmod -R 755 /var/www/html/productos_app
   ```

5. **Acceder a la aplicación**
   ```
   http://localhost/productos_app/
   ```

## Uso de la Aplicación

### Página Principal
- Visualiza todos los productos y servicios en una tabla
- Muestra diferencias de tiempo calculadas automáticamente
- Incluye barra de búsqueda para filtrar resultados
- Botones de acción para Ver, Editar y Eliminar cada elemento

### Agregar Nuevo Producto/Servicio
1. Hacer clic en "Agregar Nuevo"
2. Llenar el formulario con:
   - Nombre (requerido)
   - Tipo: Producto o Servicio (requerido)
   - Descripción (opcional)
   - Fecha de inicio (requerido)
   - Fecha de fin (requerido)
3. El sistema calcula automáticamente la diferencia en tiempo real
4. Hacer clic en "Guardar"

### Ver Detalles
- Muestra información completa del producto/servicio
- Cálculo detallado de diferencias de tiempo
- Estado actual (Pendiente, En curso, Finalizado)
- Información de creación y última actualización

### Editar Producto/Servicio
- Formulario pre-llenado con datos existentes
- Validación de fechas en tiempo real
- Actualización automática de cálculos

### Eliminar Producto/Servicio
- Confirmación antes de eliminar
- Muestra información del elemento a eliminar
- Eliminación permanente de la base de datos

### Búsqueda
- Buscar por nombre, descripción o tipo
- Resultados filtrados en tiempo real
- Opción para limpiar búsqueda

## Estructura de Base de Datos

### Tabla: productos
```sql
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo ENUM('producto', 'servicio') NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Índices
- `idx_tipo`: Índice en el campo tipo
- `idx_fecha_inicio`: Índice en fecha_inicio
- `idx_fecha_fin`: Índice en fecha_fin

## Cálculos de Fechas

La aplicación utiliza funciones PHP y MySQL para calcular diferencias:

### En PHP (Clase Producto)
```php
public function calcularDiferencias($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $diferencia = $inicio->diff($fin);
    
    return [
        'dias' => $diferencia->days,
        'meses' => $diferencia->y * 12 + $diferencia->m,
        'años' => $diferencia->y,
        'detalle' => $diferencia->format('%y años, %m meses, %d días')
    ];
}
```

### En MySQL (Consultas)
```sql
SELECT 
    DATEDIFF(fecha_fin, fecha_inicio) as diferencia_dias,
    TIMESTAMPDIFF(MONTH, fecha_inicio, fecha_fin) as diferencia_meses
FROM productos;
```

## Validaciones Implementadas

### Validaciones del Servidor (PHP)
- Campos requeridos: nombre, tipo, fecha_inicio, fecha_fin
- Validación de fechas: fecha_fin debe ser posterior a fecha_inicio
- Sanitización de datos de entrada
- Validación de tipos de datos

### Validaciones del Cliente (JavaScript)
- Cálculo en tiempo real de diferencias
- Validación de fechas antes del envío
- Mensajes de error dinámicos
- Prevención de envío de formularios inválidos

## Características de Diseño

### Interfaz de Usuario
- Diseño moderno con gradientes y sombras
- Iconos Font Awesome para mejor UX
- Bootstrap 5 para responsividad
- Animaciones CSS suaves
- Esquema de colores consistente

### Experiencia de Usuario
- Navegación intuitiva
- Mensajes de confirmación y error claros
- Carga rápida de páginas
- Compatibilidad móvil
- Accesibilidad mejorada

## Seguridad

### Medidas Implementadas
- Uso de PDO con prepared statements
- Sanitización de datos de entrada
- Validación tanto en cliente como servidor
- Protección contra inyección SQL
- Escape de caracteres HTML

## Mantenimiento

### Respaldos
- Respaldar base de datos regularmente
- Mantener copias de archivos de configuración

### Actualizaciones
- Verificar compatibilidad de PHP/MySQL
- Probar en entorno de desarrollo antes de producción

### Monitoreo
- Revisar logs de Apache/PHP regularmente
- Monitorear rendimiento de consultas MySQL

## Soporte Técnico

Para soporte técnico o reportar problemas:
1. Verificar logs de error de Apache: `/var/log/apache2/error.log`
2. Verificar logs de PHP
3. Verificar conectividad a base de datos
4. Revisar permisos de archivos

## Licencia

Esta aplicación fue desarrollada como proyecto personalizado. Todos los derechos reservados.

---

**Desarrollado con ❤️ usando PHP, MySQL, Bootstrap y JavaScript**

