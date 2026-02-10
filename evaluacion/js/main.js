function mostrarInfo(btn) {
  const card = btn.closest('.card');
  const texto = card.querySelector('.card-text');
  if (texto.innerText.includes("Ver más")) {
    texto.innerText = "¡Gracias por tu interés! Este curso incluye material digital y profesores certificados.";
  } else {
    texto.innerText = "Haz clic para conocer más sobre este curso.";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const formRegistro = document.getElementById("formRegistro");
  const alertaRegistro = document.getElementById("alertaRegistro");
  const formContacto = document.getElementById("formContacto");
  const alertaContacto = document.getElementById("alertaContacto");

  formRegistro.addEventListener("submit", (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nombre").value.trim();
    const correo = document.getElementById("correo").value.trim();
    const curso = document.getElementById("curso").value;

    if (!nombre || !correo || !curso) {
      alertaRegistro.innerHTML = '<div class="alert alert-danger">Por favor llena todos los campos.</div>';
    } else {
      alertaRegistro.innerHTML = '<div class="alert alert-success">¡Registro enviado con éxito!</div>';

      alert(
        "Registrado exitosamente\n\n" +
        "Nombre: " + nombre + "\n" +
        "Correo: " + correo + "\n" +
        "Curso: " + curso
      );

      formRegistro.reset();
    }
  });

  formContacto.addEventListener("submit", (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nombreContacto").value.trim();
    const correo = document.getElementById("correoContacto").value.trim();

    if (!nombre || !correo) {
      alertaContacto.innerHTML = '<div class="alert alert-warning">Faltan datos por llenar.</div>';
    } else {
      alertaContacto.innerHTML = '<div class="alert alert-success">Mensaje enviado correctamente.</div>';

      alert(
        "Registrado exitosamente\n\n" +
        "Nombre: " + nombre + "\n" +
        "Correo: " + correo
      );

      formContacto.reset();
    }
  });
});



function abrirVentana(curso) {
  let contenido = "";

  if (curso === "ingles") {
    contenido = `
      <h2>Curso de Inglés</h2>
      <img src="src/img/curso_ingles[1].webp" alt="Curso de Inglés" style="width:100%; border-radius:10px; margin-bottom:15px;">
      <p>Mejora tu nivel desde básico hasta avanzado con clases enfocadas en conversación, escritura y comprensión auditiva. 
      Ideal para certificaciones TOEFL o IELTS.</p>
      <p><strong>Duración:</strong> 3 meses</p>
      <p><strong>Precio:</strong> $1200 MXN / mes</p>
      <a href="registro.html" target="_blank" class="btn-inscribirse">Inscribirme</a>
    `;
  } else if (curso === "regularizacion") {
    contenido = `
      <h2>Clases de Regularización</h2>
      <img src="src/img/curso_regularizacion[1].jpg" alt="Regularización" style="width:100%; border-radius:10px; margin-bottom:15px;">
      <p>Refuerza tus materias con profesores especializados en matemáticas, física y química. 
      Ideal para alumnos de primaria, secundaria y preparatoria.</p>
      <p><strong>Duración:</strong> 2 meses</p>
      <p><strong>Precio:</strong> $1000 MXN / mes</p>
      <a href="registro.html" target="_blank" class="btn-inscribirse">Inscribirme</a>
    `;
  } else if (curso === "examen") {
    contenido = `
      <h2>Preparación Examen de Admisión</h2>
      <img src="src/img/curso_examen[1].jpg" alt="Examen de Admisión" style="width:100%; border-radius:10px; margin-bottom:15px;">
      <p>Entrenamiento intensivo con simulacros, asesorías personalizadas y estrategias de estudio. 
      ¡Aumenta tus posibilidades de ingreso a la universidad!</p>
      <p><strong>Duración:</strong> 2 meses</p>
      <p><strong>Precio:</strong> $1500 MXN / mes</p>
      <a href="registro.html" target="_blank" class="btn-inscribirse">Inscribirme</a>
    `;
  }

  const ventana = window.open("", "infoCurso", "width=520,height=480,scrollbars=yes,resizable=no");
  ventana.document.write(`
    <html>
      <head>
        <title>Información del Curso</title>
        <style>
          body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; padding: 20px; }
          h2 { color: #0d6efd; margin-bottom: 10px; }
          p { font-size: 15px; line-height: 1.6; }
          a.btn-inscribirse {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 10px;
          }
          a.btn-inscribirse:hover { background-color: #0b5ed7; }
          button {
            margin-top: 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
          }
          button:hover { background-color: #5a6268; }
        </style>
      </head>
      <body>
        ${contenido}
        <button onclick="window.close()">Cerrar</button>
      </body>
    </html>
  `);
}
