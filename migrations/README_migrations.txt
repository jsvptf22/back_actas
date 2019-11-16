pasos para generar una nueva migracion.

1) ir a la carpeta migrations que está en la raiz
    cd migrations

2) generar una migracion limpia
    ../../../vendor/bin/doctrine-migrations generate

    En windows:
    php ../../../vendor/doctrine/migrations/bin/doctrine-migrations.php migrations:generate

3) Se creará en la carpeta list un archivo Version<fecha_actual_con_hora>.php

4) realizar los respectivos ajustes en el archivo

5) Una vez modificada la migración, se debe ejecutar
    ../../../vendor/bin/doctrine-migrations migrate

    En windows:
    php ../../../vendor/doctrine/migrations/bin/doctrine-migrations.php migrations:migrate

    Opcionalmente se puede pasar el parámetro --write-sql para generar un .sql de las sentencias ejecutadas.

DEVOLVER UNA MIGRACION
    en la carpeta migrations ejecutar el comando
    ../../../vendor/bin/doctrine-migrations migrations:execute YYYYMMDDHHMMSS --down

    donde YYYYMMDDHHMMSS es <fecha_actual_con_hora> del archivo generado en el paso 2
    

Resumen:

1. Use el command de consola para generar la migracion (/ruta_saia/vendor/bin/doctrine-migrations migrations:generate)
2. Modifique la migración para cambiar la estructura de la base de datos (tablas, campos, etc).
3. Use el metodo postUp para modificar los datos (insert, update, delete)
4. Haga todos los drop al final del metodo postUp
5. Si es posible ejecute consultas SQL simples para migrar los datos
   Si un simple SQL no puede hacer el trabajo, use PHP dentro del metodo postUp ejecutando consultas con Doctrine\DBAL\Connection ($this->connection)
6. Ejecute las migraciones (/ruta_saia/vendor/bin/doctrine-migrations migrations:migrate)