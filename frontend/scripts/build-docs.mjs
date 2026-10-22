import { copyFileSync, mkdirSync, existsSync } from "node:fs";
import { createRequire } from "node:module";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const require = createRequire(import.meta.url);
const frontend = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const swagger = dirname(require.resolve("swagger-ui-dist/package.json"));
const output = resolve(frontend, "dist/docs");
mkdirSync(output, { recursive: true });

for (const file of [
  "swagger-ui.css",
  "swagger-ui-bundle.js",
  "swagger-ui-bundle.js.LICENSE.txt",
  "LICENSE",
  "NOTICE",
]) {
  if (existsSync(resolve(swagger, file))) {
    copyFileSync(resolve(swagger, file), resolve(output, file));
  }
}
copyFileSync(
  resolve(frontend, "../docs/openapi.json"),
  resolve(output, "openapi.json"),
);
console.log("Swagger UI e especificação disponíveis em /docs.");
