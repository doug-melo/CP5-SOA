async function initializeSwagger() {
  try {
    const response = await fetch("/docs/openapi.json");
    if (!response.ok)
      throw new Error("Não foi possível carregar a especificação.");
    const spec = await response.json();
    // Testar pela mesma origem permite usar também portas alternativas.
    spec.servers = [
      { url: window.location.origin, description: "Servidor atual" },
    ];
    window.ui = SwaggerUIBundle({
      spec,
      dom_id: "#swagger-ui",
      deepLinking: true,
      validatorUrl: null,
    });
  } catch (error) {
    document.getElementById("swagger-ui").textContent = error.message;
  }
}

initializeSwagger();
