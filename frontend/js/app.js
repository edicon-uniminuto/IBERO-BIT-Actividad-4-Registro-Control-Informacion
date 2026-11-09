/*
 * ============================================================
 * PROYECTO: IberoTech - Actividad 4 - Registro y control de información
 * INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
 * ESTUDIANTE: Edilberto Elias Contreras Sierra
 * DOCENTE: Jairo Niño
 * ARCHIVO: frontend/js/app.js
 * TECNOLOGÍAS: JavaScript ES6
 * DESCRIPCIÓN: Confirmación de borrado y mejoras de interacción de formularios.
 * ============================================================
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  // Se ejecuta únicamente al enviar un formulario con data-confirm.
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const message = form.getAttribute('data-confirm') || '¿Confirmar acción?';
      if (!window.confirm(message)) event.preventDefault();
    });
  });
});
