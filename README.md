# CPM — cPanel Manager

A portable PHP CLI tool for Monash **FIT3047** and **FIT3048** students who need to deploy websites on Monash cPanel.

CPM helps you **manage existing projects** and **import / create new ones**. Domain mounting, SSL, and database import / configuration still need to be done in the cPanel web UI.

## Requirements

- PHP 8 or higher (CLI), with `ext-readline`
- Git
- Composer

## Install and run

CPM is **green software**: no system-wide install and no `sudo`. That suits Monash cPanel’s terminal, where elevated privileges are usually unavailable. Copy the project onto the server and run it.

### Method 1 — ZIP via File Manager

1. Download the repository ZIP from GitHub.
2. In cPanel **File Manager**, upload and extract the archive.
3. Open Terminal (or SSH), `cd` into the extracted folder, then run:

```bash
bash cpm
```

### Method 2 — Git clone / pull

1. Clone (or pull) the repository with the GitHub URL.
2. `cd` into the project directory, then run:

```bash
bash cpm
```

You can also start with:

```bash
php bin/cpm.php
```

On first run, CPM runs a system check (PHP, Git, Composer, and common extensions).

## Deployment architecture

Each **project** contains one or more **branch environments** (folders). Every environment is a full site checkout (its own Git repo + Composer app), not just a Git branch name on a shared tree:

```text
project/
├── review/     # one environment (e.g. tracks git branch "review")
├── dev/
└── prod/
```

**Rule:** each deployed branch environment needs its own domain. Recommended subdomain pattern:

```text
[branchName].[projectName].[mainDomain]
```

Examples (replace `mainDomain` with the domain Monash allocated to you):

| Environment folder | Suggested subdomain |
|--------------------|---------------------|
| `review` | `review.myapp.[mainDomain]` |
| `dev` | `dev.myapp.[mainDomain]` |
| `prod` | `prod.myapp.[mainDomain]` |

## What CPM does

| Area | Capability |
|------|------------|
| Add Project | Import an existing project, or create a new one (clone the first environment) |
| Projects | List and open registered projects |
| Open → Git | Status / Fetch / Pull for a selected environment |
| Manage | Rename, refresh registration, repair path, remove / delete |
| Branch Management | List / add / remove / delete environments under a project |
| Settings | System check and CPM info |

Metadata locations:

- Per project: `{projectPath}/.cpm/config.json`
- Global registry: `~/.cpm/registry.json`

## What you still do in cPanel

CPM does **not** create domains, issue SSL certificates, or import / configure databases. Finish those steps in the cPanel UI.

### Attach a subdomain to a branch environment

1. Log in to **cPanel**.
2. Under **Domains**, open **Domains** → **Create a New Domain**.
3. Choose **Registered Domain**.
4. Enter: `[branchName].[projectName].[mainDomain]`.
5. Set **Document Root** to that branch environment’s web root folder.
6. Submit.
7. Wait a few seconds for the domain to finish setting up.
8. Back on the home page, under **Security**, open **SSL/TLS Status** (SSL/TLS certificates).
9. Tick the domain you just created → **Continue**, and wait for encryption to finish.
10. Wait a short while for cPanel to write the related `.htaccess` file. After that, the deployment should be reachable over HTTPS.

### Database

Create the database and user, import dumps, and wire credentials into your app in cPanel (or via your app’s env / config files). CPM does not automate this yet.

## Typical student workflow

1. Use CPM to **Add Existing** or **Add New** project and register environments.
2. Use **Branch Management → Add Branch** when you need another environment (e.g. `review`, `dev`).
3. In cPanel, create the matching subdomain and SSL for each environment.
4. Point each domain’s document root at that environment’s web root.
5. Configure the database in cPanel and connect it in the app.
6. Use CPM **Git** (fetch / pull) when you need to update code on an environment.

## License

MIT — see [LICENSE](LICENSE).
