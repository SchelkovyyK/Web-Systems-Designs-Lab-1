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


# Session 8 – Scalable Media Cloud & In-Memory Ingestion Module

## Purpose of the module
This module expands the core enterprise API with high-throughput unstructured data handling capabilities, mimicking an Instagram-style media ingestion pipeline. It introduces physical file streaming, binary asset storage optimization, advanced cursor pagination tracking, and multi-layered cache abstraction to isolate heavy computing loads from backend service routines.

## Functional requirements
- **Multi-Part File Ingestion:** Processes multi-part form data image attachments (`.jpg`, `.png`) up to 10MB using strict server-side framework input validators.
- **Asynchronous Pipeline Emulation:** Persists media payloads instantaneously under an initial tracking state (`uploaded`) before transitioning records into a optimized state (`processed`).
- **Media Asset Resolution Paths:** Automatically converts server storage disk routes into public web asset locations using isolated endpoint routers.
- **Cursor Load-More Pagination:** Feeds front-end clients using sequential collection chunks, appending lists on-demand to limit initial network strain.

## Non-Functional requirements
- **Decoupled Architecture Boundary:** Completely bans storing heavy image byte blobs inside PostgreSQL tables to eliminate memory bloat, database connection pool exhaustion, and storage latency.
- **Nginx Reverse Proxy Volume Pass-Through:** Configured Nginx to read and stream static uploaded files directly from a shared Docker host volume bridge (`/var/www/storage`), completely bypassing the PHP worker thread overhead.
- **Page-State Memory Abstraction:** Utilizes isolated, key-value page strings directly inside **Redis Database 1** (`photos.index.page.{id}`) with a 60-second expiration countdown to cache paginated JSON feed layers.
- **Hardware-Accelerated Client Views:** Employs hardware-assisted rendering optimizations (`loading="lazy"`, `decoding="async"`, `content-visibility: auto`) within the front-end rendering cycle to maintain low device performance overhead.

## Decoupled Media Streaming Flow
To optimize bandwidth usage and shield backend servers from heavy processing loops, media queries follow a zero-overhead data path:

## Endpoints
- `GET /api/78716/v1/photos` - Retrieve paginated lists of shared media assets (served from Redis memory keys).
- `POST /api/78716/v1/photos` - Multi-part file ingest handler (clears all listing cache entries instantly).
- `GET /api/78716/v1/photos/{id}` - Retrieve isolated single post item rows from memory.
- `DELETE /api/78716/v1/photos/{id}` - Wipe out metadata references, remove physical files from host disks, and clean old cache maps.

## Testing evidence
Include screenshots of:
- Postman `201 Created` file-upload form data request containing the `"processing_status": "processed"` flag.
- Full-width dark mode responsive Vue 3 application view showing the scalable multi-column grid layout.
- Terminal check running `redis-cli -n 1 KEYS *` outputting the string list keys (`photos.index.page.1`).
- Terminal check running `redis-cli -n 1 TTL` demonstrating expiration countdown execution.

# Session 9 – High-Performance Fan-Out Feed & Follower Cache Service

## Purpose of the module
This module elevates the scalable media engine into a full-scale social news feed service (mimicking Instagram/Twitter architecture). It introduces a relational subscriber graph, data aggregation pipelines, dynamic page-state memory abstraction via cursor pagination, and high-performance Redis cache key isolation to deliver sub-millisecond personalized timelines without overloading relational database clusters.

## Functional requirements
- **Social Graph Subscriptions:** Complete subscriber network endpoints allowing users to follow and unfollow creators under strict input rules (e.g., self-following blocking validations).
- **Aggregated Personalized Timeline:** Computes a specialized social feed fetching media assets exclusively from the current user's followings, combined with their own uploads, ordered chronologically.
- **Dynamic User Hint Engine:** Automates user lookup pipelines via an endpoint (`/users/hints`) to expose active database creator IDs for instant frontend interaction.
- **Cursor Paginated Streaming:** Feeds front-end clients using sequence chunk markers (`limit=4`), preventing state duplication when loading more records dynamically.

## Non-Functional requirements
- **Fan-Out-on-Read Optimization:** Employs a Pull-Model aggregation database routine combined with Redis layer memory abstraction to compute timelines at runtime with ultra-low query execution latencies.
- **Isolated Cursor Cache Serialization:** Leverages isolated, key-value storage mapping inside **Redis Database 1**, enforcing strict unique cache-key isolation by attaching encoded pagination pointers (`feed:{userId}:{limit}:{cursor}`) [INDEX].
- **Atomic Cache Flushes:** Implements global data lifecycle hooks (`Cache::flush()`) triggered automatically whenever mutation states occur (`POST /follow`, `DELETE /follow`), wiping out stale timeline configurations.
- **Virtual DOM Key Collision Defense:** Utilizes composite index identification arrays (`:key="${p.id}-${index}"`) inside Vue 3 templates to block rendering overlaps during memory-swapping intervals.

## Decoupled Feed Pagination Path
To isolate concurrent scroll-fetching events and eliminate heavy relational calculation overhead, lookups follow an adaptive cache routing loop:

## Endpoints
- `GET /api/78716/v1/feed` - Retrieve personalized, cursor-paginated timeline lists (served from dynamic Redis cache segments).
- `POST /api/78716/v1/users/{id}/follow` - Subscribe to a target creator node (invalidates global cache maps immediately).
- `DELETE /api/78716/v1/users/{id}/follow` - Terminate subscription link and wipe out computed memory allocations.
- `GET /api/78716/v1/users/hints` - Scan and retrieve unique active creator identifiers available for routing.

## Testing evidence
Include screenshots of:
- Postman or Browser developer tools tracking JSON payloads displaying structured `"next_cursor": "..."` strings alongside nested resource layers.
- Vue 3 Front-end News Feed UI displaying the safety block message `✨ You have caught up with everything!` once the timeline payload returns `next_cursor: null`.
- Terminal check running `docker compose exec redis redis-cli -n 1 KEYS "*feed*"` proving successful generation of dynamic isolated pagination cursor hashes inside Database 1.
- Terminal check running `redis-cli -n 1 KEYS "*"` demonstrating empty or modified indices right after hitting the `➕ Follow` / `❌ Unfollow` trigger commands.