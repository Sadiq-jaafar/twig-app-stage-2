# Docker + Render deployment

This repository can be deployed to Render using the included `Dockerfile`. The Docker image sets ownership and permissions for `data/` so the app can write JSON files at runtime.

Quick steps (Render - Docker):

1. Commit and push this branch to GitHub.

2. In Render, create a new service:
   - Service type: "Web Service"
   - Environment: "Docker"
   - Connect your GitHub repo and branch
   - Build Command: leave blank (Render will build the Docker image)
   - Start Command: leave blank (the Dockerfile defines the CMD)

3. Render will build the Docker image using the repository's `Dockerfile`. The image uses the `www-data` user and ensures `data/` is owned by `www-data`, preventing permission errors when the app writes `data/users.json`.

Local testing:

Build locally:
```powershell
docker build -t twig-app .
docker run -p 8000:8000 -e PORT=8000 twig-app
```

Open http://localhost:8000 and try the signup flow. The app will store JSON files in the container's `data/` directory (not on your host unless you mount a volume).

Notes:
- This Docker setup is intended for small deployments or demos. For production use consider a proper web server (nginx + php-fpm) and persistent storage (managed DB or object store) because container filesystem can be ephemeral across redeploys.
- If you prefer the app to run as a different user, update the Dockerfile.
