<?php
/**
 * Componente reutilizable: Footer del sitio
 * Incluir al final de cada página PHP antes de </body>
 */
?>
</main><!-- /.main -->

<!-- FOOTER -->
<footer class="site-footer" aria-label="Pie de página">
  <div class="container">
    <div class="footer__main">

      <div>
        <p class="footer__brand-logo">PAKAL</p>
        <p class="footer__brand-tagline">La Distinción del Tiempo</p>
        <p class="footer__brand-desc">
          Moda premium inspirada en la grandeza de la civilización maya. Cada prenda es un homenaje a una cultura que marcó la historia de la humanidad.
        </p>
        <div class="footer__social" aria-label="Redes sociales">
          <a href="#" class="footer__social-link" aria-label="Instagram">IG</a>
          <a href="#" class="footer__social-link" aria-label="Facebook">FB</a>
          <a href="#" class="footer__social-link" aria-label="TikTok">TK</a>
          <a href="#" class="footer__social-link" aria-label="Pinterest">PT</a>
        </div>
      </div>

      <div>
        <h4 class="footer__col-title">Comprar</h4>
        <ul class="footer__col-links">
          <li><a href="/proyecto/productos.php?orden=nuevo">Novedades</a></li>
          <li><a href="/proyecto/productos.php?genero=hombre">Hombre</a></li>
          <li><a href="/proyecto/productos.php?genero=mujer">Mujer</a></li>
          <li><a href="/proyecto/productos.php?genero=infantil">Infantil</a></li>
          <li><a href="/proyecto/productos.php?rebaja=1">Rebajas</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer__col-title">Información</h4>
        <ul class="footer__col-links">
          <li><a href="/proyecto/acerca.php#historia">Quiénes somos</a></li>
          <li><a href="/proyecto/acerca.php#sostenibilidad">Sostenibilidad</a></li>
          <li><a href="/proyecto/acerca.php#artesanos">Artesanos PAKAL</a></li>
          <li><a href="/proyecto/acerca.php#prensa">Prensa</a></li>
          <li><a href="/proyecto/acerca.php#trabaja">Trabaja con nosotros</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer__col-title">Ayuda</h4>
        <ul class="footer__col-links">
          <li><a href="/proyecto/ayuda.php">Centro de ayuda</a></li>
          <li><a href="/proyecto/ayuda.php#tallas">Tallas y guías</a></li>
          <li><a href="/proyecto/ayuda.php#envios">Envíos y entregas</a></li>
          <li><a href="/proyecto/ayuda.php#devoluciones">Devoluciones</a></li>
          <li><a href="/proyecto/ayuda.php#contacto">Contacto</a></li>
        </ul>
      </div>

    </div>

    <div class="footer__bottom">
      <p>© <?= date('Y') ?> PAKAL. Todos los derechos reservados.</p>
      <div class="footer__bottom-links">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<script src="/proyecto/js/main.js"></script>
</body>
</html>
