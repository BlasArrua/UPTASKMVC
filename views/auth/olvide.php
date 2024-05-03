<div class="contenedor olvide">
    <?php include_once __DIR__.'/../templates/nombre-sitio.php'; ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recupera el Acceso Uptask</p>
        <?php include_once __DIR__.'/../templates/alertas.php'; ?>

        <form method="POST" action="/olvide" class="formulario" novalidate>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Tu Email">
            </div>
            <input type="submit" class="boton" value="Enviar">
        </form>

        <div class="acciones">
            <a href="/">¿Ya tienes una cuenta? Iniciar Sesion</a>
            <a href="/crear">¿Aún no tienes una cuenta? Crear</a>
        </div>
    </div> <!--fin contenedor-sm -->
</div>