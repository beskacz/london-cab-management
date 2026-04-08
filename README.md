<p align="center">
    <a href="https://gibbonedu.org/" target="_blank"><img width="200" src="https://gibbonedu.org/img/gibbon-logo.png"></a><br>
    Gibbon is a flexible, open source school management platform designed <br>
    to make life better for teachers, students, parents and schools.
</p>

------

Gibbon Core
===========
The Core repository represents the bulk of Gibbon, including all of its primary functionality. The core can be extended through the use of modules and themes, which are provided separately. See the [Extend](https://gibbonedu.org/extend/) page for more info.

Gibbon is open source, and maintained for the benefit of teachers, students, parents and schools.

## Docker (local development)

This repository includes **Docker Compose** for a quick local stack: **Apache + PHP 8.2 + MySQL 8**.

1. Copy environment defaults (optional): `cp .env.example .env`
2. Start: `docker compose up --build`
3. Open **http://127.0.0.1:8080** (or the port in `GIBBON_HTTP_PORT`; bound to loopback only).
4. On first boot, the app container runs `composer install` if `vendor/` is missing.
5. Run the **Gibbon installer** in the browser. Use this database connection:
   - **Host:** `db` (the Compose service name)
   - **Database / user / password:** match `.env` (defaults: `gibbon` / `gibbon` / `gibbon`, root password `gibbon_root`)

MySQL is exposed on **localhost:3306** by default (`MYSQL_PORT`) for GUI clients. Data is stored in the `gibbon_mysql_data` volume.

### HTTPS with [Portless](https://github.com/vercel-labs/portless) (recommended)

**Yes — use Portless for HTTPS.** The Compose stack only serves **plain HTTP** on the loopback port (Apache in the `app` container). You do **not** need a second container (Caddy, Traefik, etc.) for TLS if you use Portless: it runs a **local HTTPS reverse proxy** (TLS on **443** by default), terminates TLS, and forwards to `http://127.0.0.1:8080` (or whatever `GIBBON_HTTP_PORT` is).

1. Install (requires Node.js 20+): `npm install -g portless`
2. Start the stack: `docker compose up --build`
3. Register the Gibbon HTTP port with Portless (must match `GIBBON_HTTP_PORT` in `.env`, default **8080**):

   ```bash
   portless alias gibbon 8080
   ```

4. Run `portless list` and open the URL for `gibbon` — usually **`https://gibbon.localhost`** with no port in the bar. The first time, Portless may install/trust a **local CA** so the browser accepts the certificate.
5. In Gibbon’s installer (or **System Admin → System Settings**), set the **absolute / base URL** to that same **`https://…`** origin so cookies, redirects, and links stay correct.

The app is bound to **`127.0.0.1:${GIBBON_HTTP_PORT}`** so only local tools (including the Portless proxy) can hit it.

**Plain HTTP only:** use `http://127.0.0.1:8080` if you skip Portless.

## Documentation

For full documentation, visit [docs.gibbonedu.org](https://docs.gibbonedu.org).

## Installation & Support

For installation instructions, visit [Getting Started: Installing Gibbon](https://docs.gibbonedu.org/introduction/installing-gibbon)

For support visit [ask.gibbonedu.org](https://ask.gibbonedu.org) or see [our documentation](https://docs.gibbonedu.org).

## Cutting Edge
If you want to run the latest version of Gibbon, prerelease, you can get the source from our [GitHub repository](https://github.com/GibbonEdu/core). Remember, though, it is not stable, and you may lose data. This is not for the faint of heart.

For installation instructions, be sure to follow the instructions for [Cutting Edge Code](https://docs.gibbonedu.org/introduction/installation-options/cutting-edge-code).

## Translation

Thanks to our amazing volunteers, Gibbon is available in many different languages. We use the online tool [POEditor](https://poeditor.com), which enables our volunteer translators to collaborate and track their translation progress. Huge thanks to POEditor for their support of open source projects and making this tool available for our community. If you would like to help translate Gibbon, please email support@gibbonedu.org and [learn more here](https://gibbonedu.org/about/#languages). Your help would be most appreciated!

## Contributing

We welcome community contribution and aim to ensure Gibbon is an open and friendly environment. Information about contributing, submitting issues, and pull requests can be found in the following docs:

- [**Contributor Guide**](https://github.com/GibbonEdu/core/blob/master/.github/CONTRIBUTING.md) - Learn more about how you can contribute to Gibbon, from code to non-code contributions alike.

- [**Code of Conduct**](https://github.com/GibbonEdu/core/blob/master/.github/CODE_OF_CONDUCT.md) - Our pledge to foster a welcoming community and a positive environment for anyone to participate in.

- [**Developer Workflow**](https://docs.gibbonedu.org/development/getting-started/developer-workflow) - If you want to get involved in the development process, check out our workflow and [GitHub repository](https://github.com/GibbonEdu/core). Generally there will be a development branch with the latest code, as per our [Development Road Map](https://docs.gibbonedu.org/development/gibbon-road-map).

## License

Gibbon is licensed under GNU General Public License v3.0. You can obtain a copy of the license [here](https://github.com/GibbonEdu/core/blob/master/LICENSE).
