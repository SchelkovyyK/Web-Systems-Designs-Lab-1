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


# Session 7 – Geographic Proximity Module

## Purpose of the module
This module expands the core API with spatial geolocation capabilities, letting users manage locations (restaurants) and execute high-performance spatial proximity query lookups around specific coordinate points using database-driven mathematics and advanced pagination routing.

## Functional requirements
- **Data Ingestion:** Automatically streams and syncs data from external geographic sources (OpenStreetMap Overpass API) [INDEX] with built-in hardcoded local package fallbacks.
- **RESTful CRUD Operations:** Complete data management endpoints bound strictly under the versioned student prefix namespace.
- **Proximity Search:** Executes geometric searches matching filtered city boundaries, coordinates (`lat`/`lng`), and exact search radiuses.
- **Data Pagination:** Enforces strict pagination boundaries (`->paginate(50)`) on backend payloads to handle highly populated data structures gracefully.

## Non-functional requirements
- **Database Math Inversion:** Shifts heavy Haversine spherical trigonometric operations (\(\sin\), \(\cos\), \(\arccos\)) entirely onto the PostgreSQL engine rather than evaluating arrays in PHP memory.
- **Performance Indexing:** Employs explicit B-Tree database-level indexing on string coordinates (`city`) to keep search lookups highly performant.
- **Isolated Redis Layering:** Configures sub-level indexing mapped directly onto **Redis Database 1** (`REDIS_CACHE_DB=1`) to isolate geographic transient lookups from primary project task queues [INDEX].
- **Cache-Aside Dynamic Invalidation:** Flushes and invalidates spatial caches globally whenever location state data mutations (`POST`, `PUT`, `DELETE`) are processed [INDEX].

## Haversine Query Core Architecture
To identify locations within a custom radius while accounting for the curved spherical shape of the Earth, the PostgreSQL layer runs the raw Haversine calculation directly in milliseconds:

```sql
SELECT *, (6371 * acos(
    cos(radians(target_lat)) * cos(radians(latitude)) *
    cos(radians(longitude) - radians(target_lng)) +
    sin(radians(target_lat)) * sin(radians(latitude))
)) AS distance_km
FROM restaurants
WHERE city = 'katowice' AND distance_km <= target_radius
```

## Endpoints
- `GET /api/78716/v1/restaurants` - Fetch all saved locations (JSON API).
- `POST /api/78716/v1/restaurants` - Register a new spatial location.
- `GET /api/78716/v1/restaurants/{id}` - Fetch details of a specific restaurant.
- `PUT /api/78716/v1/restaurants/{id}` - Modify attributes of a location entry.
- `DELETE /api/78716/v1/restaurants/{id}` - Destroy a resource node and flush Redis memory allocations [INDEX].
- `GET /api/78716/v1/restaurants/nearby` - Execute proximity queries using parameters (`city`, `lat`, `lng`, `radius`, `page`).

## Testing evidence
Include screenshots of:
- Successful dynamic database seeding execution logs (OpenStreetMap/Fallback response tracker).
- Interactive Vue 3 Frontend web app routing page (`/labs`) showing 50 paginated records from Katowice.
- `redis-cli` active terminal view showing generated lookup hashes cached inside `SELECT 1`.
- Terminal validation outputs tracking empty cache matrices after executing a `PUT` mutation command [INDEX].
