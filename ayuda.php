<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: ayuda.php                                         ║
// ║  PROPÓSITO: Centro de ayuda / preguntas frecuentes          ║
// ║                                                              ║
// ║  Página informativa estática con secciones navegables por ancla:
// ║  - #tallas       → Guía de tallas y medidas                 ║
// ║  - #envios       → Información sobre envíos y tiempos        ║
// ║  - #devoluciones → Política de devoluciones y cambios        ║
// ║  - #contacto     → Formas de contactar al soporte            ║
// ║                                                              ║
// ║  No consulta la base de datos porque es contenido fijo.      ║
// ╚══════════════════════════════════════════════════════════════╝

require_once 'config/session.php';
require_once 'config/database.php';

$pageTitle = 'Centro de Ayuda — PAKAL';
$activeNav = '';
require_once 'includes/header.php';
?>

<!-- HERO AYUDA -->
<section class="help-hero" aria-label="Centro de ayuda">
  <div class="help-hero__inner container">
    <span class="section-label">Estamos aquí</span>
    <h1 class="help-hero__title">¿En qué podemos <em>ayudarte?</em></h1>
    <p>Encuentra respuestas sobre pedidos, envíos, tallas y devoluciones. Si no encuentras lo que buscas, nuestro equipo está a un mensaje de distancia.</p>
  </div>
</section>

<!-- ACCESOS RÁPIDOS -->
<section class="help-quicklinks container" aria-label="Accesos rápidos">
  <div class="help-quicklinks__grid">
    <a href="#envios" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <h3>Envíos</h3>
      <p>Plazos, costos y zonas de entrega</p>
    </a>
    <a href="#devoluciones" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
      </div>
      <h3>Devoluciones</h3>
      <p>Política de cambios y reembolsos</p>
    </a>
    <a href="#tallas" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18M15 3v18"/></svg>
      </div>
      <h3>Guía de Tallas</h3>
      <p>Cómo elegir tu talla perfecta</p>
    </a>
    <a href="#pagos" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <h3>Pagos</h3>
      <p>Métodos de pago aceptados</p>
    </a>
    <a href="#pedidos" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <h3>Mis Pedidos</h3>
      <p>Seguimiento y gestión de órdenes</p>
    </a>
    <a href="#contacto" class="help-ql-card">
      <div class="help-ql-card__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.82 19a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.73 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 18z"/></svg>
      </div>
      <h3>Contacto</h3>
      <p>Habla con nuestro equipo</p>
    </a>
  </div>
</section>

<!-- ENVÍOS -->
<section class="help-section" id="envios" aria-label="Información de envíos">
  <div class="container">
    <div class="maya-line" aria-hidden="true">
      <span class="maya-glyph">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="2" y="2" width="28" height="28" stroke="currentColor" stroke-width="0.8"/>
          <rect x="8" y="8" width="16" height="16" stroke="currentColor" stroke-width="0.5" opacity="0.5"/>
          <rect x="13" y="13" width="6" height="6" fill="currentColor" opacity="0.4"/>
        </svg>
      </span>
    </div>
    <span class="section-label">Logística</span>
    <h2 class="section-title">Envíos <em>& Entregas</em></h2>

    <div class="help-content-grid">
      <div class="help-faq">
        <details class="faq-item" open>
          <summary class="faq-item__q">¿Cuánto tarda en llegar mi pedido?</summary>
          <div class="faq-item__a">
            <p>Los tiempos de entrega dependen de tu ubicación:</p>
            <ul class="help-list">
              <li><strong>Mérida y área metropolitana:</strong> 24–48 horas hábiles</li>
              <li><strong>Interior de Yucatán, Campeche y Quintana Roo:</strong> 2–4 días hábiles</li>
              <li><strong>Resto de México:</strong> 3–7 días hábiles</li>
              <li><strong>Internacional:</strong> 7–21 días hábiles (disponible próximamente)</li>
            </ul>
            <p style="margin-top:12px;">Los pedidos realizados antes de las 2:00 p.m. se procesan el mismo día hábil.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Cuál es el costo de envío?</summary>
          <div class="faq-item__a">
            <ul class="help-list">
              <li><strong>Envío gratuito</strong> en pedidos mayores a <strong>$2,500 MXN</strong></li>
              <li><strong>$99 MXN</strong> — Mérida y alrededores</li>
              <li><strong>$149 MXN</strong> — Interior de la Península</li>
              <li><strong>$199 MXN</strong> — Resto de México</li>
            </ul>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Cómo puedo rastrear mi pedido?</summary>
          <div class="faq-item__a">
            <p>Una vez que tu pedido sea enviado, recibirás un correo con el número de guía y el enlace de rastreo de la paquetería asignada (FedEx, DHL o Estafeta según tu zona).</p>
            <p style="margin-top:12px;">También puedes consultar el estado de tu pedido iniciando sesión en tu cuenta PAKAL en la sección <a href="/proyecto/auth.php" style="color:var(--gold);text-decoration:underline;">Mis Pedidos</a>.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Hacen envíos a toda la República?</summary>
          <div class="faq-item__a">
            <p>Sí, enviamos a los 32 estados de México. Para zonas extendidas o comunidades de difícil acceso, el tiempo de entrega puede variar. Contáctanos antes de hacer tu pedido si tienes dudas sobre tu localidad.</p>
          </div>
        </details>
      </div>

      <div class="help-info-box">
        <h4>Paqueterías aliadas</h4>
        <p>Trabajamos con las mejores paqueterías del país para garantizar que tu prenda llegue en perfectas condiciones:</p>
        <ul class="help-list" style="margin-top:12px;">
          <li>FedEx Express</li>
          <li>DHL Parcel</li>
          <li>Estafeta</li>
          <li>Mensajería local en Mérida</li>
        </ul>
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
          <p style="font-size:0.78rem;color:var(--gray-mid);">Todos los pedidos incluyen seguro de envío sin costo adicional. En caso de pérdida o daño, nos hacemos responsables.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DEVOLUCIONES -->
<section class="help-section help-section--alt" id="devoluciones" aria-label="Devoluciones y cambios">
  <div class="container">
    <span class="section-label">Sin complicaciones</span>
    <h2 class="section-title">Devoluciones <em>& Cambios</em></h2>

    <div class="help-content-grid">
      <div class="help-faq">
        <details class="faq-item" open>
          <summary class="faq-item__q">¿Cuántos días tengo para hacer una devolución?</summary>
          <div class="faq-item__a">
            <p>Tienes <strong>30 días naturales</strong> desde la fecha de recepción de tu pedido para solicitar una devolución o cambio. Sin preguntas, sin complicaciones.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Qué condiciones debe tener la prenda?</summary>
          <div class="faq-item__a">
            <ul class="help-list">
              <li>Sin usar y sin lavar</li>
              <li>Con todas las etiquetas originales</li>
              <li>En su empaque original o equivalente</li>
              <li>Sin daños adicionales por parte del cliente</li>
            </ul>
            <p style="margin-top:12px;"><strong>Excepción:</strong> Las prendas bordadas a mano bajo pedido especial no son elegibles para devolución por cambio de opinión, solo por defecto de fabricación.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Cómo inicio una devolución?</summary>
          <div class="faq-item__a">
            <ol class="help-list help-list--ordered">
              <li>Inicia sesión en tu cuenta y ve a <a href="/proyecto/auth.php" style="color:var(--gold);">Mis Pedidos</a></li>
              <li>Selecciona el pedido y haz clic en "Solicitar devolución"</li>
              <li>Indica el motivo y adjunta fotos si hay defecto</li>
              <li>Recibirás una etiqueta de devolución por correo en 24 h</li>
              <li>Deposita el paquete en el punto indicado</li>
              <li>Tu reembolso o cambio se procesa en 5–7 días hábiles</li>
            </ol>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿El reembolso es completo?</summary>
          <div class="faq-item__a">
            <p>Sí. Si el motivo de devolución es un defecto de fabricación o error nuestro, el reembolso es total, incluido el costo de envío original.</p>
            <p style="margin-top:12px;">Si la devolución es por cambio de opinión o talla, el reembolso es del 100% del valor del producto. El costo del envío de devolución puede descontarse si el monto de la compra es menor a $2,500 MXN.</p>
          </div>
        </details>
      </div>

      <div class="help-info-box">
        <h4>¿Prenda con defecto?</h4>
        <p>Si tu prenda llegó con algún defecto de confección o bordado, escríbenos de inmediato. Nos comprometemos a:</p>
        <ul class="help-list" style="margin-top:12px;">
          <li>Reemplazo inmediato sin costo</li>
          <li>Reembolso total si no hay disponibilidad</li>
          <li>Coordinamos la recolección en tu domicilio</li>
        </ul>
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
          <a href="#contacto" class="btn btn--sm btn--solid">Reportar un problema</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TALLAS -->
<section class="help-section" id="tallas" aria-label="Guía de tallas">
  <div class="container">
    <span class="section-label">Viste perfecto</span>
    <h2 class="section-title">Guía de <em>Tallas</em></h2>
    <p style="max-width:600px;margin-bottom:48px;">Nuestras prendas siguen la talla estándar mexicana. Para prendas artesanales con bordado o corte tradicional, recomendamos subir una talla si tienes dudas.</p>

    <div class="talla-tables">

      <div class="talla-table-wrap">
        <h4 style="margin-bottom:16px;font-family:var(--font-body);font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;">Parte Superior (Blusas, Camisas, Sacos)</h4>
        <table class="talla-table">
          <thead>
            <tr><th>Talla</th><th>Busto (cm)</th><th>Cintura (cm)</th><th>Cadera (cm)</th></tr>
          </thead>
          <tbody>
            <tr><td>XS</td><td>80–84</td><td>62–66</td><td>87–91</td></tr>
            <tr><td>S</td><td>84–88</td><td>66–70</td><td>91–95</td></tr>
            <tr><td>M</td><td>88–92</td><td>70–74</td><td>95–99</td></tr>
            <tr><td>L</td><td>92–98</td><td>74–80</td><td>99–105</td></tr>
            <tr><td>XL</td><td>98–106</td><td>80–88</td><td>105–113</td></tr>
            <tr><td>XXL</td><td>106–114</td><td>88–96</td><td>113–121</td></tr>
          </tbody>
        </table>
      </div>

      <div class="talla-table-wrap">
        <h4 style="margin-bottom:16px;font-family:var(--font-body);font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;">Pantalones & Faldas</h4>
        <table class="talla-table">
          <thead>
            <tr><th>Talla</th><th>Cintura (cm)</th><th>Cadera (cm)</th><th>Largo (cm)</th></tr>
          </thead>
          <tbody>
            <tr><td>XS / 34</td><td>62–66</td><td>87–91</td><td>98</td></tr>
            <tr><td>S / 36</td><td>66–70</td><td>91–95</td><td>99</td></tr>
            <tr><td>M / 38</td><td>70–74</td><td>95–99</td><td>100</td></tr>
            <tr><td>L / 40</td><td>74–80</td><td>99–105</td><td>101</td></tr>
            <tr><td>XL / 42</td><td>80–88</td><td>105–113</td><td>102</td></tr>
            <tr><td>XXL / 44</td><td>88–96</td><td>113–121</td><td>103</td></tr>
          </tbody>
        </table>
      </div>

    </div>

    <div class="help-info-box" style="max-width:560px;margin-top:32px;">
      <h4>¿Cómo tomar tus medidas?</h4>
      <ol class="help-list help-list--ordered" style="margin-top:12px;">
        <li><strong>Busto:</strong> Mide alrededor de la parte más ancha del pecho, manteniendo la cinta horizontal.</li>
        <li><strong>Cintura:</strong> Mide en el punto más estrecho del torso, generalmente 2–3 cm sobre el ombligo.</li>
        <li><strong>Cadera:</strong> Mide alrededor de la parte más ancha de las caderas y glúteos.</li>
      </ol>
      <p style="margin-top:16px;font-size:0.8rem;color:var(--gray-mid);">Si estás entre dos tallas, para prendas con bordado artesanal recomendamos la talla mayor.</p>
    </div>
  </div>
</section>

<!-- PAGOS -->
<section class="help-section help-section--alt" id="pagos" aria-label="Métodos de pago">
  <div class="container">
    <span class="section-label">Transacciones seguras</span>
    <h2 class="section-title">Métodos de <em>Pago</em></h2>

    <div class="help-content-grid">
      <div class="help-faq">
        <details class="faq-item" open>
          <summary class="faq-item__q">¿Qué métodos de pago aceptan?</summary>
          <div class="faq-item__a">
            <ul class="help-list">
              <li><strong>Tarjetas de crédito:</strong> Visa, Mastercard, American Express</li>
              <li><strong>Tarjetas de débito:</strong> Visa Débito, Mastercard Débito</li>
              <li><strong>Transferencia bancaria:</strong> SPEI en tiempo real</li>
              <li><strong>OXXO Pay:</strong> Pago en efectivo en tiendas OXXO</li>
              <li><strong>Mercado Pago:</strong> Disponible próximamente</li>
            </ul>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Es seguro pagar en PAKAL?</summary>
          <div class="faq-item__a">
            <p>Sí. Utilizamos cifrado SSL de 256 bits en toda nuestra plataforma, el mismo estándar de la banca en línea. Nunca almacenamos datos de tarjetas en nuestros servidores. Todos los pagos pasan por pasarelas certificadas con PCI DSS.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Puedo pagar a meses sin intereses?</summary>
          <div class="faq-item__a">
            <p>Próximamente habilitaremos mensualidades sin intereses a 3 y 6 meses con las principales tarjetas de crédito en compras mayores a $1,500 MXN. Suscríbete a nuestro boletín para ser el primero en saberlo.</p>
          </div>
        </details>

        <details class="faq-item">
          <summary class="faq-item__q">¿Qué pasa si mi pago falla?</summary>
          <div class="faq-item__a">
            <p>Si tu pago no se procesa correctamente, tu pedido no se genera. Puedes intentar de nuevo con otro método de pago o contactarnos para ayudarte. Nunca se realizan cargos duplicados.</p>
          </div>
        </details>
      </div>

      <div class="help-info-box">
        <h4>Facturación</h4>
        <p>¿Necesitas factura? Solícitala dentro de los primeros 5 días hábiles después de tu compra enviando tu RFC y datos fiscales a:</p>
        <p style="margin-top:12px;"><a href="mailto:facturacion@pakal.mx" style="color:var(--gold);">facturacion@pakal.mx</a></p>
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
          <p style="font-size:0.78rem;color:var(--gray-mid);">Emitimos CFDI 4.0 con todos los usos fiscales disponibles. El RFC debe incluir homoclave y dirección fiscal completa.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PEDIDOS -->
<section class="help-section" id="pedidos" aria-label="Gestión de pedidos">
  <div class="container">
    <span class="section-label">Tu cuenta</span>
    <h2 class="section-title">Mis <em>Pedidos</em></h2>

    <div class="help-faq" style="max-width:760px;">
      <details class="faq-item" open>
        <summary class="faq-item__q">¿Puedo modificar o cancelar mi pedido?</summary>
        <div class="faq-item__a">
          <p>Puedes modificar o cancelar tu pedido dentro de las primeras <strong>2 horas</strong> tras realizarlo, siempre que no haya sido enviado aún. Contáctanos de inmediato por WhatsApp o correo.</p>
          <p style="margin-top:10px;">Una vez que el pedido entra a proceso de envío, ya no es posible cancelarlo; en ese caso aplica nuestra política de devoluciones.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__q">No recibí confirmación de mi pedido, ¿qué hago?</summary>
        <div class="faq-item__a">
          <p>Revisa tu carpeta de spam. Si en 30 minutos no recibes el correo de confirmación, contáctanos indicando el correo con el que compraste y el importe cargado. Verificamos tu pedido de inmediato.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__q">Mi pedido llegó incompleto o con una prenda equivocada</summary>
        <div class="faq-item__a">
          <p>Lamentamos el inconveniente. Escríbenos a <a href="mailto:ayuda@pakal.mx" style="color:var(--gold);">ayuda@pakal.mx</a> con fotos del paquete y su contenido. En menos de 24 horas coordinamos el envío correcto sin costo adicional.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__q">¿Cómo obtengo mi historial de compras?</summary>
        <div class="faq-item__a">
          <p>Al iniciar sesión en tu cuenta PAKAL puedes ver todos tus pedidos anteriores, su estado y la factura correspondiente. Si no tienes cuenta, crea una con el mismo correo con el que compraste y tu historial se vinculará automáticamente.</p>
        </div>
      </details>
    </div>
  </div>
</section>

<!-- CONTACTO -->
<section class="help-contact" id="contacto" aria-label="Contacto">
  <div class="container">
    <div class="maya-line" aria-hidden="true">
      <span class="maya-glyph">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="0.8"/>
          <circle cx="16" cy="16" r="7" stroke="currentColor" stroke-width="0.8"/>
          <rect x="13" y="13" width="6" height="6" fill="currentColor" opacity="0.5"/>
        </svg>
      </span>
    </div>
    <span class="section-label" style="display:flex;justify-content:center;">Estamos aquí</span>
    <h2 class="section-title" style="text-align:center;">Habla con <em>nuestro equipo</em></h2>
    <p style="text-align:center;max-width:560px;margin:0 auto 48px;">Nuestros estilistas y asesores están disponibles de lunes a sábado de 9:00 a.m. a 8:00 p.m. (hora del centro de México).</p>

    <div class="contact-channels">
      <div class="contact-channel">
        <div class="contact-channel__icon" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h4>WhatsApp</h4>
        <p>Respuesta en menos de 1 hora en horario de atención.</p>
        <a href="https://wa.me/529991234567" target="_blank" rel="noopener" class="btn btn--sm btn--solid" style="margin-top:16px;">+52 (999) 123-4567</a>
      </div>

      <div class="contact-channel">
        <div class="contact-channel__icon" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <h4>Correo electrónico</h4>
        <p>Respondemos en un plazo máximo de 24 horas hábiles.</p>
        <a href="mailto:ayuda@pakal.mx" class="btn btn--sm" style="margin-top:16px;">ayuda@pakal.mx</a>
      </div>

      <div class="contact-channel">
        <div class="contact-channel__icon" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <h4>Visítanos</h4>
        <p>Showroom en Mérida, Yucatán. Cita previa requerida.</p>
        <a href="#" class="btn btn--sm" style="margin-top:16px;">Agendar visita</a>
      </div>
    </div>

    <!-- Formulario de contacto -->
    <div class="contact-form-wrap">
      <h3 style="text-align:center;margin-bottom:32px;font-family:var(--font-display);font-size:1.6rem;font-weight:400;">
        Envíanos un <em>mensaje</em>
      </h3>
      <form class="contact-form" onsubmit="return pakalContactSubmit(event);" novalidate>
        <div class="contact-form__row">
          <div class="contact-form__group">
            <label for="cf-nombre">Nombre completo</label>
            <input type="text" id="cf-nombre" name="nombre" placeholder="Tu nombre" required autocomplete="name">
          </div>
          <div class="contact-form__group">
            <label for="cf-email">Correo electrónico</label>
            <input type="email" id="cf-email" name="email" placeholder="tu@correo.com" required autocomplete="email">
          </div>
        </div>
        <div class="contact-form__group">
          <label for="cf-asunto">Asunto</label>
          <select id="cf-asunto" name="asunto" required>
            <option value="" disabled selected>Selecciona un tema</option>
            <option>Estado de mi pedido</option>
            <option>Devolución o cambio</option>
            <option>Problema con mi pago</option>
            <option>Información de producto</option>
            <option>Quiero ser diseñador en PAKAL</option>
            <option>Prensa y colaboraciones</option>
            <option>Otro</option>
          </select>
        </div>
        <div class="contact-form__group">
          <label for="cf-mensaje">Mensaje</label>
          <textarea id="cf-mensaje" name="mensaje" rows="5" placeholder="Cuéntanos en qué podemos ayudarte..." required></textarea>
        </div>
        <div style="text-align:center;">
          <button type="submit" class="btn btn--lg btn--solid">Enviar mensaje</button>
        </div>
        <div id="cf-feedback" class="contact-form__feedback" aria-live="polite" style="display:none;"></div>
      </form>
    </div>

  </div>
</section>

<script>
function pakalContactSubmit(e) {
  e.preventDefault();
  const fb = document.getElementById('cf-feedback');
  fb.style.display = 'block';
  fb.style.color = 'var(--gold)';
  fb.style.textAlign = 'center';
  fb.style.padding = '16px';
  fb.style.marginTop = '24px';
  fb.style.fontSize = '0.85rem';
  fb.style.letterSpacing = '0.06em';
  fb.textContent = 'Mensaje recibido. Te responderemos en menos de 24 horas. ¡Gracias!';
  e.target.reset();
  return false;
}

// Acordeón FAQ: abrir/cerrar
document.querySelectorAll('.faq-item').forEach(item => {
  item.addEventListener('toggle', () => {
    if (item.open) {
      document.querySelectorAll('.faq-item[open]').forEach(other => {
        if (other !== item) other.removeAttribute('open');
      });
    }
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>
