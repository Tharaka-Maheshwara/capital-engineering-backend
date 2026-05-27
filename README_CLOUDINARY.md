Cloudinary integration — setup & verification
==========================================

Quick summary
-------------
- This Laravel backend integrates Cloudinary for project images.
- Key files:
  - `App\Services\CloudinaryService` — SDK wrapper
  - `App\Services\ProjectService` — handles create/update/delete and image flow
  - `App\Jobs\UploadProjectImage` — queued upload job (optional)
  - Filament resource: `app/Filament/Resources/ProjectResource.php`

Environment
-----------
Add to your `.env`:

```
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
# Optional: enable async queued uploads
CLOUDINARY_ASYNC=false
```

Manual verification
-------------------
1. Start app: `php artisan serve`
2. Create a project via API (multipart):

```bash
curl -X POST 'http://localhost:8000/api/v1/projects' \
  -H 'Accept: application/json' \
  -F 'title=Test Project' \
  -F 'description=Testing upload' \
  -F 'status=planning' \
  -F 'location=Colombo' \
  -F 'client=Test' \
  -F 'featured_image=@/path/to/your/image.jpg'
```

3. Confirm DB record:

```
php artisan tinker
>>> \App\Models\Project::latest()->first()->only(['featured_image_url','featured_image_public_id','featured_image_alt']);
```

4. Check Cloudinary Media Library for uploaded asset.

Production notes
----------------
- Enable `CLOUDINARY_ASYNC=true` and configure Laravel queues for large/slow uploads.
- Use upload presets and signed uploads for better security (set presets in Cloudinary dashboard and use `CLOUDINARY_UPLOAD_PRESET` env var).
- Use transform URLs (already exposed via `featured_image_thumbnail` and `featured_image_og`) for SEO/OG.
- Add monitoring and retries for queue jobs; consider Sentry for error reporting.
- Add scheduled job to clean orphaned assets periodically if needed.

Tests
-----
- A feature test `tests/Feature/ProjectImageUploadTest.php` is included and binds a fake `CloudinaryService` to avoid external calls.
