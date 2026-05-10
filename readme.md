# Web Systems Design - Project 5

## API Versioning
The API has been versioned to ensure backward compatibility and follow REST best practices.
- **Base URL:** `/api/78716/v1`
- **Student ID:** 78716

## API Standardized Responses

### HTTP Status Codes
The system strictly follows HTTP semantics:
- `200 OK` – Successful retrieval or update.
- `201 Created` – Successful creation of a new task.
- `204 No Content` – Successful deletion (no response body).
- `404 Not Found` – Resource does not exist.
- `422 Unprocessable Entity` – Validation failed.

### Consistent Error Format
All errors are returned in a unified JSON format using the `ApiError` helper class:

```json
{
    "error": {
        "status": 404,
        "message": "Task with ID 99999 not found",
        "path": "/api/78716/v1/tasks/99999"
    }
}
```

## Endpoints
- `GET /tasks` - Get list of all tasks (with Redis caching).
- `POST /tasks` - Create a new task (validates title, album_number, and priority).
- `GET /tasks/{id}` - Get details of a specific task.
- `PUT /tasks/{id}` - Update a task.
- `DELETE /tasks/{id}` - Remove a task and invalidate cache.

## Performance Optimization
- **Caching:** Implemented using the **Cache-Aside pattern** with Redis.
- **Cache Invalidation:** The `tasks.index` cache is automatically cleared whenever a task is created, updated, or deleted.
