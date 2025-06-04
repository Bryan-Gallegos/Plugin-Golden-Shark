# 📡 API Interna – Golden Shark Admin Panel

**Última actualización:** 2025-05-30

Esta API interna permite a desarrolladores y sistemas externos interactuar con el plugin **Golden Shark Admin Panel** mediante una clave de autenticación privada.

---

## 🔐 Autenticación

Todas las solicitudes deben incluir el siguiente encabezado:

```
X-GS-API-Key: TU_CLAVE_API
```

Esta clave se genera automáticamente en la configuración del plugin y no debe compartirse públicamente.

---

## 📨 POST /wp-json/golden-shark/v1/leads

Registra un nuevo lead en el sistema.

### ✅ Ejemplo de solicitud:

**URL:** `/wp-json/golden-shark/v1/leads`  
**Método:** `POST`  
**Headers:**
```
Content-Type: application/json
X-GS-API-Key: TU_CLAVE_API
```

**Body JSON:**
```json
{
  "nombre": "Carlos Gómez",
  "correo": "carlos@example.com",
  "mensaje": "Estoy interesado en un evento"
}
```

### 🔄 Respuesta esperada:
```json
{ "success": true }
```

---

## 📨 GET /wp-json/golden-shark/v1/leads

Devuelve la lista completa de leads registrados.

### ✅ Ejemplo de solicitud:

**URL:** `/wp-json/golden-shark/v1/leads`  
**Método:** `GET`  
**Headers:**
```
X-GS-API-Key: TU_CLAVE_API
```

### 🔄 Respuesta esperada:
```json
[
  {
    "nombre": "Carlos Gómez",
    "correo": "carlos@example.com",
    "mensaje": "Estoy interesado en un evento",
    "fecha": "2025-05-30 10:15:00"
  },
  ...
]
```

---

## 📅 GET /wp-json/golden-shark/v1/eventos

Devuelve la lista completa de eventos registrados.

### ✅ Ejemplo de solicitud:

**URL:** `/wp-json/golden-shark/v1/eventos`  
**Método:** `GET`  
**Headers:**
```
X-GS-API-Key: TU_CLAVE_API
```

### 🔄 Respuesta esperada:
```json
[
  {
    "titulo": "Congreso Golden Shark 2025",
    "fecha": "2025-06-01",
    "lugar": "Lima",
    "tipo": "lanzamiento"
  },
  ...
]
```

---

## 📌 Notas

- El acceso está restringido a usuarios con la clave API registrada.
- El plugin puede extenderse fácilmente con nuevos endpoints (`/tareas`, `/notas`, etc.)
- Para mayor seguridad, se recomienda cambiar la clave periódicamente.

---

Desarrollado por: **Carlos Gallegos**