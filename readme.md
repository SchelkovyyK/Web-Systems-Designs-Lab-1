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


# Session 6 – URL Shortener Module

## Purpose of the module
This module allows users to create short URLs from long URLs and redirect users from a short code to the original URL.

## Functional requirements
The system accepts a long URL and returns a short URL.  
The system redirects users from a short code to the original URL.  
The system validates the original URL.  
The system stores URL mappings in PostgreSQL.  
The system counts redirects using click_count.

## Non-functional requirements
The redirect should be fast.  
The short code should be unique.  
The API should return consistent JSON responses.  
The system should use correct HTTP status codes.  
The list endpoint should use Redis cache.

## Base62 encoding
Base62 converts numeric IDs into short text codes using digits, lowercase letters, and uppercase letters. This is useful because database IDs can be represented as short readable strings.

## Endpoints
POST /api/78716/v1/short-links  
GET /api/78716/v1/short-links  
GET /api/78716/v1/short-links/{id}  
GET /r/{code}

## Testing evidence
Include screenshots of:
- successful POST request
- successful GET list request
- 422 validation error
- 302 redirect response
- 404 missing short code response
- Redis DBSIZE


## URL Shortener Module

The project now includes a simple URL shortener module.

Current endpoints:
- POST /api/78716/v1/short-links
- GET /api/78716/v1/short-links
- GET /api/78716/v1/short-links/{id}
- GET /r/{code}

The module uses PostgreSQL for persistent storage, Redis for caching the list endpoint, and Base62 encoding for generating short codes from numeric database IDs.