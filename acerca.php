<?php
require_once 'config/session.php';
require_once 'config/database.php';

$pageTitle = 'Quiénes Somos — PAKAL';
$activeNav = '';
require_once 'includes/header.php';
?>

<!-- HERO ACERCA -->
<section class="about-hero" aria-label="Nuestra historia">
  <div class="about-hero__inner container">
    <span class="section-label">Raíces · Visión · Comunidad</span>
    <h1 class="about-hero__title">
      Somos PAKAL.<br>
      <em>La voz del diseño maya contemporáneo.</em>
    </h1>
    <p class="about-hero__subtitle">
      Una plataforma nacida en la Península de Yucatán para unir a diseñadores de moda con raíces mayas y a quienes buscan vestir con identidad, historia y distinción.
    </p>
  </div>
  <div class="about-hero__line" aria-hidden="true"></div>
</section>

<!-- NUESTRA HISTORIA -->
<section class="about-section container" id="historia" aria-label="Nuestra historia">
  <div class="about-section__grid">
    <div class="about-section__text">
      <span class="section-label">Nuestra Historia</span>
      <h2 class="section-title">Un proyecto que nació <em>de la tierra</em></h2>
      <p>
        PAKAL toma su nombre de K'inich Janaab' Pakal, el gran gobernante maya de Palenque cuyo legado de arte, arquitectura y sabiduría permanece intacto siglos después. Ese espíritu de permanencia es el que guía nuestra plataforma.
      </p>
      <p style="margin-top:16px;">
        Fundada en Mérida, Yucatán, PAKAL nació de una pregunta sencilla: ¿por qué los diseñadores de moda de la Península de Yucatán —con su enorme talento y su profunda conexión cultural— no tenían un espacio propio para darse a conocer al mundo?
      </p>
      <p style="margin-top:16px;">
        Hoy somos ese espacio. Una vitrina digital donde artistas, diseñadoras, bordadoras y creativos de toda la Península pueden mostrar sus colecciones, contar sus historias y conectar con clientes que valoran la moda con propósito.
      </p>
    </div>
    <div class="about-section__glyph" aria-hidden="true">
      <svg viewBox="0 0 240 280" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Estela maya estilizada -->
        <rect x="60" y="10" width="120" height="260" stroke="var(--border)" stroke-width="1"/>
        <rect x="70" y="20" width="100" height="240" stroke="var(--gold)" stroke-width="0.5" opacity="0.4"/>
        <!-- Glifo central -->
        <rect x="90" y="60" width="60" height="60" stroke="var(--gold)" stroke-width="1" opacity="0.7"/>
        <rect x="100" y="70" width="40" height="40" fill="var(--gold)" opacity="0.12"/>
        <line x1="90" y1="60" x2="150" y2="120" stroke="var(--gold)" stroke-width="0.5" opacity="0.5"/>
        <line x1="150" y1="60" x2="90" y2="120" stroke="var(--gold)" stroke-width="0.5" opacity="0.5"/>
        <circle cx="120" cy="90" r="8" fill="var(--gold)" opacity="0.5"/>
        <!-- Franjas decorativas mayas -->
        <rect x="70" y="140" width="100" height="4" fill="var(--gold)" opacity="0.2"/>
        <rect x="70" y="152" width="100" height="2" fill="var(--gold)" opacity="0.1"/>
        <rect x="70" y="162" width="100" height="4" fill="var(--gold)" opacity="0.2"/>
        <!-- Texto inferior -->
        <text x="120" y="210" font-family="Georgia,serif" font-size="7" letter-spacing="4" fill="var(--gold)" opacity="0.5" text-anchor="middle">PAKAL</text>
        <text x="120" y="224" font-family="Georgia,serif" font-size="5" letter-spacing="3" fill="var(--gray-light)" opacity="0.7" text-anchor="middle">YUCATÁN · MÉXICO</text>
      </svg>
    </div>
  </div>
</section>

<!-- CULTURA MAYA -->
<section class="about-maya" id="cultura" aria-label="Raíces de la cultura maya">
  <div class="container">
    <div class="maya-line" aria-hidden="true">
      <span class="maya-glyph">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="0.8"/>
          <circle cx="16" cy="16" r="7" stroke="currentColor" stroke-width="0.8"/>
          <line x1="16" y1="2" x2="16" y2="9" stroke="currentColor" stroke-width="0.8"/>
          <line x1="16" y1="23" x2="16" y2="30" stroke="currentColor" stroke-width="0.8"/>
          <line x1="2" y1="16" x2="9" y2="16" stroke="currentColor" stroke-width="0.8"/>
          <line x1="23" y1="16" x2="30" y2="16" stroke="currentColor" stroke-width="0.8"/>
          <rect x="13" y="13" width="6" height="6" fill="currentColor" opacity="0.5"/>
        </svg>
      </span>
    </div>

    <span class="section-label" style="display:flex;justify-content:center;">La Civilización que nos Inspira</span>
    <h2 class="section-title" style="text-align:center;max-width:640px;margin:0 auto 16px;">
      El legado maya vive en <em>cada hilo</em>
    </h2>
    <p style="text-align:center;max-width:680px;margin:0 auto 60px;">
      La civilización maya floreció durante más de tres mil años en lo que hoy conocemos como la Península de Yucatán, Chiapas, Guatemala, Belice y Honduras. Su herencia no es pasado: es presente vivo.
    </p>

    <div class="maya-pillars">

      <article class="maya-pillar">
        <div class="maya-pillar__icon" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="24,4 44,44 4,44" stroke="currentColor" stroke-width="1" fill="none"/>
            <polygon points="24,14 36,44 12,44" stroke="currentColor" stroke-width="0.5" opacity="0.5" fill="none"/>
            <line x1="4" y1="44" x2="44" y2="44" stroke="currentColor" stroke-width="1"/>
          </svg>
        </div>
        <h3>Arquitectura Sagrada</h3>
        <p>
          Las pirámides de Chichén Itzá, Uxmal y Palenque no son solo monumentos: son calendarios en piedra, mapas del cosmos y tratados de astronomía. Sus proporciones y geometrías inspiran los cortes y estructuras de nuestras prendas.
        </p>
      </article>

      <article class="maya-pillar">
        <div class="maya-pillar__icon" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Telar simplificado -->
            <rect x="10" y="8" width="28" height="32" stroke="currentColor" stroke-width="1" fill="none"/>
            <line x1="10" y1="16" x2="38" y2="16" stroke="currentColor" stroke-width="0.5"/>
            <line x1="10" y1="24" x2="38" y2="24" stroke="currentColor" stroke-width="0.5"/>
            <line x1="10" y1="32" x2="38" y2="32" stroke="currentColor" stroke-width="0.5"/>
            <line x1="18" y1="8" x2="18" y2="40" stroke="currentColor" stroke-width="0.5"/>
            <line x1="26" y1="8" x2="26" y2="40" stroke="currentColor" stroke-width="0.5"/>
            <line x1="34" y1="8" x2="34" y2="40" stroke="currentColor" stroke-width="0.5"/>
          </svg>
        </div>
        <h3>Textiles Ancestrales</h3>
        <p>
          El huipil, la faja y el hipil son prendas con siglos de historia. Los bordados en punto de cruz y la técnica del brocado maya encierran cosmologías enteras: flores, aves del paraíso, serpientes emplumadas y maíz sagrado tejidos con hilo de seda y algodón nativo.
        </p>
      </article>

      <article class="maya-pillar">
        <div class="maya-pillar__icon" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Sol / Kin -->
            <circle cx="24" cy="24" r="10" stroke="currentColor" stroke-width="1" fill="none"/>
            <circle cx="24" cy="24" r="3" fill="currentColor" opacity="0.5"/>
            <line x1="24" y1="4" x2="24" y2="11" stroke="currentColor" stroke-width="1"/>
            <line x1="24" y1="37" x2="24" y2="44" stroke="currentColor" stroke-width="1"/>
            <line x1="4" y1="24" x2="11" y2="24" stroke="currentColor" stroke-width="1"/>
            <line x1="37" y1="24" x2="44" y2="24" stroke="currentColor" stroke-width="1"/>
            <line x1="9.5" y1="9.5" x2="14.5" y2="14.5" stroke="currentColor" stroke-width="0.8"/>
            <line x1="33.5" y1="33.5" x2="38.5" y2="38.5" stroke="currentColor" stroke-width="0.8"/>
            <line x1="38.5" y1="9.5" x2="33.5" y2="14.5" stroke="currentColor" stroke-width="0.8"/>
            <line x1="9.5" y1="38.5" x2="14.5" y2="33.5" stroke="currentColor" stroke-width="0.8"/>
          </svg>
        </div>
        <h3>K'in — El Tiempo Eterno</h3>
        <p>
          Para los mayas, el tiempo no avanza en línea recta sino en ciclos. El Tzolk'in (calendario sagrado de 260 días) y el Haab' (calendario solar de 365 días) son sistemas de medición del cosmos que revelan una comprensión del universo sin igual en la historia humana.
        </p>
      </article>

      <article class="maya-pillar">
        <div class="maya-pillar__icon" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Ixchel / luna -->
            <path d="M36 24 A14 14 0 1 1 36 24.01" stroke="currentColor" stroke-width="1" fill="none"/>
            <path d="M32 14 A10 10 0 1 0 32 34" stroke="currentColor" stroke-width="0.5" fill="none" opacity="0.4"/>
            <circle cx="22" cy="24" r="3" fill="currentColor" opacity="0.4"/>
          </svg>
        </div>
        <h3>Ixchel, Diosa del Tejido</h3>
        <p>
          Ixchel, diosa de la luna, la medicina y el tejido, era la patrona de las tejedoras mayas. Las mujeres le ofrendaban hilos de colores en su santuario en la Isla de Cozumel. Su espíritu continúa guiando a las artesanas de Yucatán que hoy bordan a mano cada pieza.
        </p>
      </article>

    </div>
  </div>
</section>

<!-- MISIÓN / VISIÓN / VALORES -->
<section class="about-values container" id="mision" aria-label="Misión y valores">
  <div class="about-values__header">
    <span class="section-label">Lo que nos mueve</span>
    <h2 class="section-title">Plataforma para <em>diseñadores</em>, creada por diseñadores</h2>
  </div>
  <div class="about-values__grid">

    <div class="about-value-card">
      <span class="about-value-card__num">01</span>
      <h3>Misión</h3>
      <p>Conectar a diseñadores de moda de la Península de Yucatán con un mercado nacional e internacional, ofreciéndoles una plataforma digital que respeta y amplifica su identidad cultural maya.</p>
    </div>

    <div class="about-value-card">
      <span class="about-value-card__num">02</span>
      <h3>Visión</h3>
      <p>Convertirnos en el referente de la moda con raíces mayas, donde cada colección sea un diálogo entre la herencia ancestral y la vanguardia contemporánea, poniendo a Yucatán en el mapa global del diseño de moda.</p>
    </div>

    <div class="about-value-card">
      <span class="about-value-card__num">03</span>
      <h3>Comunidad</h3>
      <p>No somos una tienda más. Somos un ecosistema: diseñadores independientes, artesanas bordadoras, tejedoras de henequén y jóvenes creativos que comparten un mismo propósito: vestir con historia y con alma.</p>
    </div>

  </div>
</section>

<!-- SOSTENIBILIDAD -->
<section class="about-sustain" id="sostenibilidad" aria-label="Sostenibilidad">
  <div class="about-sustain__inner container">
    <div class="about-sustain__text">
      <span class="section-label">Compromiso</span>
      <h2 class="section-title">Moda que <em>cuida</em> lo que honra</h2>
      <p>La cultura maya fue la primera en habitar y cuidar estas tierras. En PAKAL, ese respeto por la naturaleza es parte del ADN de cada prenda.</p>
      <ul class="about-sustain__list">
        <li>
          <span class="about-sustain__dot"></span>
          <div>
            <strong>Fibras naturales de Yucatán</strong>
            <p>Henequén, algodón nativo y seda regional. Materiales que la tierra peninsular ha producido por siglos.</p>
          </div>
        </li>
        <li>
          <span class="about-sustain__dot"></span>
          <div>
            <strong>Tintes naturales</strong>
            <p>Añil, palo de tinte (palo de Campeche), cochinilla y achiote: la paleta de colores que los mayas utilizaron durante milenios.</p>
          </div>
        </li>
        <li>
          <span class="about-sustain__dot"></span>
          <div>
            <strong>Producción de pequeña escala</strong>
            <p>Apoyamos talleres familiares y cooperativas de artesanas en Valladolid, Ticul, Maxcanú y Motul, garantizando comercio justo.</p>
          </div>
        </li>
        <li>
          <span class="about-sustain__dot"></span>
          <div>
            <strong>Empaque biodegradable</strong>
            <p>Todo pedido llega en bolsas de fibra de henequén o papel reciclado. Cero plástico de un solo uso.</p>
          </div>
        </li>
      </ul>
    </div>
    <div class="about-sustain__visual" aria-hidden="true">
      <div class="maya-border" style="margin-bottom:32px;opacity:0.3;"></div>
      <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:160px;margin:0 auto;display:block;">
        <!-- Hoja / naturaleza estilizada maya -->
        <circle cx="100" cy="100" r="90" stroke="var(--gold)" stroke-width="0.5" opacity="0.3"/>
        <circle cx="100" cy="100" r="60" stroke="var(--gold)" stroke-width="0.5" opacity="0.2"/>
        <path d="M100 20 C140 60 140 140 100 180 C60 140 60 60 100 20Z" stroke="var(--gold)" stroke-width="1" fill="var(--gold)" fill-opacity="0.05"/>
        <line x1="100" y1="20" x2="100" y2="180" stroke="var(--gold)" stroke-width="0.5" opacity="0.4"/>
        <line x1="100" y1="60" x2="130" y2="100" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <line x1="100" y1="80" x2="125" y2="110" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <line x1="100" y1="100" x2="120" y2="120" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <line x1="100" y1="60" x2="70" y2="100" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <line x1="100" y1="80" x2="75" y2="110" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <line x1="100" y1="100" x2="80" y2="120" stroke="var(--gold)" stroke-width="0.3" opacity="0.3"/>
        <circle cx="100" cy="100" r="4" fill="var(--gold)" opacity="0.6"/>
      </svg>
      <div class="maya-border" style="margin-top:32px;opacity:0.3;"></div>
    </div>
  </div>
</section>

<!-- ARTESANOS PAKAL -->
<section class="about-artisans container" id="artesanos" aria-label="Artesanos PAKAL">
  <div class="about-artisans__header">
    <span class="section-label">Nuestros Creadores</span>
    <h2 class="section-title">Los <em>artesanos</em> detrás de cada prenda</h2>
    <p style="max-width:620px;margin:0 auto;">
      En PAKAL cada diseñador es un narrador. Sus colecciones son capítulos de una historia más grande: la del pueblo maya que sigue creando, innovando y resistiendo a través del arte de vestir.
    </p>
  </div>

  <div class="about-artisans__grid">

    <article class="artisan-card">
      <div class="artisan-card__region">Mérida, Yucatán</div>
      <h3 class="artisan-card__discipline">Diseño Contemporáneo</h3>
      <p class="artisan-card__desc">
        Diseñadores urbanos que reinterpretan la greca maya, el xicalcoliuhqui y la celosía yucateca en siluetas minimalistas y de temporada.
      </p>
    </article>

    <article class="artisan-card">
      <div class="artisan-card__region">Valladolid, Yucatán</div>
      <h3 class="artisan-card__discipline">Bordado en Punto de Cruz</h3>
      <p class="artisan-card__desc">
        Bordadoras que transmiten de madres a hijas el arte de representar el universo en hilo. Cada motivo floral es un código ancestral que habla de fertilidad, agua y vida.
      </p>
    </article>

    <article class="artisan-card">
      <div class="artisan-card__region">Ticul, Yucatán</div>
      <h3 class="artisan-card__discipline">Huipil & Terno</h3>
      <p class="artisan-card__desc">
        Maestras del terno yucateco y el huipil festivo, prendas declaradas Patrimonio Cultural Inmaterial de México. Cada pieza requiere semanas de bordado a mano.
      </p>
    </article>

    <article class="artisan-card">
      <div class="artisan-card__region">Campeche</div>
      <h3 class="artisan-card__discipline">Tejido en Henequén</h3>
      <p class="artisan-card__desc">
        Artesanos que trabajan el "oro verde" de Yucatán: el henequén. Bolsas, cinturones y accesorios tejidos con la misma técnica que abasteció al mundo entero en el siglo XIX.
      </p>
    </article>

    <article class="artisan-card">
      <div class="artisan-card__region">Quintana Roo</div>
      <h3 class="artisan-card__discipline">Tintes Naturales</h3>
      <p class="artisan-card__desc">
        Tintoreros que rescatan recetas prehispánicas: cochinilla para el rojo carmesí, añil para el azul profundo, palo de tinte para los marrones terrosos de la selva maya.
      </p>
    </article>

    <article class="artisan-card artisan-card--cta">
      <div class="artisan-card__region">¿Eres diseñador?</div>
      <h3 class="artisan-card__discipline">Únete a PAKAL</h3>
      <p class="artisan-card__desc">
        Si eres diseñador, artesano o creativo de la Península de Yucatán y quieres mostrar tu trabajo en nuestra plataforma, escríbenos.
      </p>
      <a href="/proyecto/ayuda.php#contacto" class="btn btn--sm" style="margin-top:20px;">Contáctanos</a>
    </article>

  </div>
</section>

<!-- PRENSA -->
<section class="about-press" id="prensa" aria-label="Prensa y colaboraciones">
  <div class="container">
    <span class="section-label" style="display:flex;justify-content:center;">Prensa &amp; Colaboraciones</span>
    <h2 class="section-title" style="text-align:center;">¿Quieres contar <em>nuestra historia</em>?</h2>
    <p style="text-align:center;max-width:580px;margin:0 auto 40px;">
      Para solicitudes de medios, colaboraciones editoriales, entrevistas o contenido para redes sociales, contáctanos directamente.
    </p>
    <div style="text-align:center;display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="mailto:prensa@pakal.mx" class="btn btn--lg">prensa@pakal.mx</a>
      <a href="/proyecto/ayuda.php#contacto" class="btn btn--lg" style="border-color:var(--gray-light);color:var(--gray-mid);">Formulario de contacto</a>
    </div>
  </div>
</section>

<!-- TRABAJA CON NOSOTROS -->
<section class="about-jobs container" id="trabaja" aria-label="Trabaja con nosotros">
  <div class="about-jobs__inner">
    <span class="section-label">Únete al equipo</span>
    <h2 class="section-title">Trabaja <em>con nosotros</em></h2>
    <p>
      Buscamos personas apasionadas por la moda, la cultura maya y la tecnología. Si crees en que el diseño puede ser un acto de resistencia cultural y quieres ser parte de algo significativo, queremos conocerte.
    </p>
    <div class="about-jobs__positions">
      <div class="job-card">
        <h4>Fotografía Editorial</h4>
        <p>Fotógrafo/a con sensibilidad para la moda y conocimiento del patrimonio visual yucateco.</p>
        <span class="job-card__tag">Freelance · Mérida</span>
      </div>
      <div class="job-card">
        <h4>Gestión de Diseñadores</h4>
        <p>Coordinador/a que acompañe a nuevos diseñadores en su proceso de incorporación a la plataforma.</p>
        <span class="job-card__tag">Tiempo completo · Remoto</span>
      </div>
      <div class="job-card">
        <h4>Desarrollo Web</h4>
        <p>Desarrollador/a PHP con experiencia en e-commerce y gusto por los detalles de UI/UX.</p>
        <span class="job-card__tag">Freelance · Remoto</span>
      </div>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="mailto:talento@pakal.mx" class="btn btn--lg">Enviar CV a talento@pakal.mx</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
