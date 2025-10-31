<? php
/ **
 * El Unzipper extrae archivos .zip o .rar y archivos .gz en servidores web.
* Es útil si no tiene acceso al shell. Ej. Si quieres subir mucho
 * de archivos (marco php o colección de imágenes) como un archivo para ahorrar tiempo.
 * A partir de la versión 0.1.0, también admite la creación de archivos.
 *
 * @autor Andreas Tasch, en [tec], attec.at
 * @license GNU GPL v3
 * @package attec.toolbox
 * @version 0.1.1
 * /
define ( 'VERSION' , '0.1.1' );

$ timestart = microtime ( VERDADERO );
$ GLOBALS [ 'estado' ] = matriz ();

$ unzipper = nuevo  Unzipper ;
if ( isset ( $ _POST [ 'dounzip' ])) {
  // Compruebe si se seleccionó un archivo para descomprimirlo.
  $ archivo = isset ( $ _POST [ 'zipfile' ])? strip_tags ( $ _POST [ 'zipfile' ]): '' ;
  $ destino = isset ( $ _POST [ 'extpath' ])? strip_tags ( $ _POST [ 'extpath' ]): '' ;
  $ unzipper -> prepareExtraction ( $ archivo , $ destino );
}

if ( isset ( $ _POST [ 'dozip' ])) {
  $ zippath =! vacío ( $ _POST [ 'zippath' ])? strip_tags ( $ _POST [ 'zippath' ]): '.' ;
  // Archivo zip resultante, por ejemplo, zipper--2016-07-23--11-55.zip.
  $ zipfile = 'cremallera-' . fecha ( "Ymd - Hola" ). '.zip' ;
  Cremallera :: zipDir ( $ zippath , $ zipfile );
}

$ timeend = microtime ( VERDADERO );
$ time = round ( $ timeend - $ timestart , 4 );

/ **
 * Clase Unzipper
 * /
class  Unzipper {
  public  $ localdir = '.' ;
  public  $ zipfiles = array ();

   función  pública __construct () {
    // Leer el directorio y elegir archivos .zip, .rar y .gz.
    if ( $ dh = opendir ( $ this -> localdir )) {
      while (( $ archivo = readdir ( $ dh ))! == FALSE ) {
        if ( pathinfo ( $ archivo , PATHINFO_EXTENSION ) === 'zip'
          || pathinfo ( $ archivo , PATHINFO_EXTENSION ) === 'gz'
          || pathinfo ( $ archivo , PATHINFO_EXTENSION ) === 'rar'
        ) {
          $ esto -> zipfiles [] = $ archivo ;
        }
      }
      cerradoir ( $ dh );

      if (! empty ( $ this -> zipfiles )) {
        $ GLOBALS [ 'status' ] = array ( 'info' => 'archivos .zip o .gz o .rar encontrados, listos para la extracción' );
      }
      else {
        $ GLOBALS [ 'status' ] = array ( 'info' => 'No se encontraron archivos .zip, .gz o rar. Por lo tanto, solo está disponible la funcionalidad de compresión.' );
      }
    }
  }

  / **
   * Preparar y verificar zipfile para extracción.
   *
   * @param string $ archivo
   * El nombre del archivo, incluida la extensión del archivo. Por ejemplo, my_archive.zip.
   * @param string $ destino
   * La ruta de destino relativa donde extraer los archivos.
   * /
   función  pública prepareExtraction ( $ archivo , $ destino = '' ) {
    // Determinar rutas.
    if ( vacío ( $ destino )) {
      $ extpath = $ esto -> localdir ;
    }
    else {
      $ extpath = $ esto -> localdir . '/' . $ destino ;
      // Todo: mueve esto a la función de extracción.
      if (! is_dir ( $ extpath )) {
        mkdir ( $ extpath );
      }
    }
    // Solo se permite extraer archivos locales existentes.
    if ( in_array ( $ archivo , $ esto -> zipfiles )) {
      self :: extract ( $ archivo , $ extpath );
    }
  }

  / **
   * Comprueba la extensión del archivo y llama a las funciones de extracción adecuadas.
   *
   * @param string $ archivo
   * El nombre del archivo, incluida la extensión del archivo. Por ejemplo, my_archive.zip.
   * @param string $ destino
   * La ruta de destino relativa donde extraer los archivos.
   * /
   extracto de función estática  pública ( $ archivo , $ destino ) { 
    $ ext = pathinfo ( $ archivo , PATHINFO_EXTENSION );
    cambiar ( $ ext ) {
      caso  'zip' :
        self :: extractZipArchive ( $ archivo , $ destino );
        romper ;
      caso  'gz' :
        self :: extractGzipFile ( $ archivo , $ destino );
        romper ;
      caso  'rar' :
        self :: extractRarArchive ( $ archivo , $ destino );
        romper ;
    }

  }

  / **
   * Descomprima / extraiga un archivo zip usando ZipArchive.
   *
   * @param $ archivo
   * @param $ destino
   * /
   función estática  pública extractZipArchive ( $ archivo , $ destino ) { 
    // Compruebe si el servidor web admite la descompresión.
    if (! class_exists ( 'ZipArchive' )) {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: Su versión de PHP no admite la función de descomprimir.' );
      volver ;
    }

    $ zip = nuevo  ZipArchive ;

    // Verifica si el archivo es legible.
    if ( $ zip -> open ( $ archive ) === TRUE ) {
      // Compruebe si se puede escribir en el destino
      if ( is_writeable ( $ destino . '/' )) {
        $ zip -> extractTo ( $ destino );
        $ zip -> cerrar ();
        $ GLOBALS [ 'status' ] = array ( 'success' => 'Archivos descomprimidos correctamente' );
      }
      else {
        $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: el servidor web no puede escribir en el directorio.' );
      }
    }
    else {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: No se puede leer el archivo .zip.' );
    }
  }

  / **
   * Descomprime un archivo .gz.
   *
   * @param string $ archivo
   * El nombre del archivo, incluida la extensión del archivo. Por ejemplo, my_archive.zip.
   * @param string $ destino
   * La ruta de destino relativa donde extraer los archivos.
   * /
   función estática  pública extractGzipFile ( $ archivo , $ destino ) { 
    // Comprueba si zlib está habilitado
    if (! function_exists ( 'gzopen' )) {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: Su PHP no tiene habilitado el soporte zlib.' );
      volver ;
    }

    $ filename = pathinfo ( $ archivo , PATHINFO_FILENAME );
    $ gzipped = gzopen ( $ archivo , "rb" );
    $ archivo = fopen ( $ destino . '/' . $ nombre de archivo , "w" );

    while ( $ string = gzread ( $ gzipped , 4096 )) {
      fwrite ( $ archivo , $ cadena , strlen ( $ cadena ));
    }
    gzclose ( $ gzip );
    fclose ( $ archivo );

    // Compruebe si se extrajo el archivo.
    if ( file_exists ( $ destino . '/' . $ nombre de archivo )) {
      $ GLOBALS [ 'status' ] = array ( 'success' => 'Archivo descomprimido correctamente.' );

      // Si tuviéramos un archivo tar.gz, extraigamos ese archivo tar.
      if ( pathinfo ( $ destino . '/' . $ nombre de archivo , PATHINFO_EXTENSION ) == 'tar' ) {
        $ phar = new  PharData ( $ destino . '/' . $ nombre de archivo );
        if ( $ phar -> extractTo ( $ destino )) {
          $ GLOBALS [ 'status' ] = array ( 'success' => 'Archivo tar.gz extraído con éxito.' );
          // Eliminar .tar.
          desvincular ( $ destino . '/' . $ nombre de archivo );
        }
      }
    }
    else {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error al descomprimir el archivo.' );
    }

  }

  / **
   * Descomprime / extrae un archivo Rar usando RarArchive.
   *
   * @param string $ archivo
   * El nombre del archivo, incluida la extensión del archivo. Por ejemplo, my_archive.zip.
   * @param string $ destino
   * La ruta de destino relativa donde extraer los archivos.
   * /
   función estática  pública extractRarArchive ( $ archivo , $ destino ) { 
    // Compruebe si el servidor web admite la descompresión.
    if (! class_exists ( 'RarArchive' )) {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: Su versión de PHP no admite la funcionalidad de archivo .rar. <A class = "info" href = "http://php.net/manual/en /rar.installation.php "target =" _ blank "> Cómo instalar RarArchive </a> ' );
      volver ;
    }
    // Verifica si el archivo es legible.
    if ( $ rar = RarArchive :: open ( $ archivo )) {
      // Compruebe si se puede escribir en el destino
      if ( is_writeable ( $ destino . '/' )) {
        $ entradas = $ rar -> getEntries ();
        foreach ( $ entradas  como  $ entrada ) {
          $ entrada -> extraer ( $ destino );
        }
        $ rar -> cerrar ();
        $ GLOBALS [ 'status' ] = array ( 'success' => 'Archivos extraídos con éxito.' );
      }
      else {
        $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: el servidor web no puede escribir en el directorio.' );
      }
    }
    else {
      $ GLOBALS [ 'status' ] = array ( 'error' => 'Error: No se puede leer el archivo .rar.' );
    }
  }

}

/ **
 * Cremallera de clase
 *
 * Copiado y ligeramente modificado de http://at2.php.net/manual/en/class.ziparchive.php#110719
 * @autor umbalaconmeogia
 * /
class  Zipper {
  / **
   * Agregue archivos y subdirectorios en una carpeta al archivo zip.
   *
   * @param string $ carpeta
   * Ruta a la carpeta que debe estar comprimida.
   *
   * @param ZipArchive $ zipFile
   * Zipfile donde terminan los archivos.
   *
   * @param int $ exclusiveLength
   * Número de texto que se excluirá de la ruta del archivo.
   * /
   función estática  privada folderToZip ( $ carpeta , & $ zipFile , $ exclusiveLength ) { 
    $ handle = opendir ( $ carpeta );

    while ( FALSE ! == $ f = readdir ( $ handle )) {
      // Compruebe la ruta local / principal o el archivo comprimido en sí y omita.
      if ( $ f ! = '.' && $ f ! = '..' && $ f ! = nombre base (__FILE__)) {
        $ filePath = "$ carpeta / $ f" ;
        // Elimina el prefijo de la ruta del archivo antes de agregarlo al zip.
        $ localPath = substr ( $ filePath , $ exclusiveLength );

        if ( is_file ( $ filePath )) {
          $ zipFile -> addFile ( $ filePath , $ localPath );
        }
        elseif ( es_dir ( $ filePath )) {
          // Agregar subdirectorio.
          $ zipFile -> addEmptyDir ( $ localPath );
          auto :: folderToZip ( $ rutaArchivo , $ ZipFile , $ exclusiveLength );
        }
      }
    }
    closedir ( $ mango );
  }

  / **
   * Comprima una carpeta (incluido él mismo).
   *
   * Uso:
   * Zipper :: zipDir ('ruta / a / sourceDir', 'ruta / a / out.zip');
   *
   * @param cadena $ sourcePath
   * Ruta relativa del directorio a comprimir.
   *
   * @param cadena $ outZipPath
   * Ruta relativa del archivo zip de salida resultante.
   * /
   zipDir función estática  pública ( $ sourcePath , $ outZipPath ) { 
    $ pathInfo = pathinfo ( $ sourcePath );
    $ parentPath = $ pathInfo [ 'dirname' ];
    $ dirName = $ pathInfo [ 'nombre base' ];

    $ z = nuevo  ZipArchive ();
    $ Z -> abierto ( $ outZipPath , ZipArchive :: CREATE );
    $ z -> addEmptyDir ( $ dirName );
    if ( $ sourcePath == $ dirName ) {
      self :: folderToZip ( $ sourcePath , $ z , 0 );
    }
    else {
      self :: folderToZip ( $ sourcePath , $ z , strlen ( "$ parentPath /" ));
    }
    $ z -> cerrar ();

    $ GLOBALS [ 'status' ] = array ( 'success' => 'Archivo creado con éxito' . $ OutZipPath );
  }
}
?>

<! DOCTYPE html >
< html >
< cabeza >
  < título > Descomprimidor de archivos + Cremallera </ título >
  < meta  http-equiv = " Content-Type " content = " text / html; charset = UTF-8 " >
  < style  type = " text / css " >
    <! - -
    cuerpo {
      fuente - familia: Arial ,  sans-serif ;
      altura de línea : 150%;
    }

    etiqueta {
      pantalla : bloque;
      margen superior : 20 px ;
    }

    fieldset {
      borde : 0 ;
      color de fondo : # EEE ;
      margen : 10 px  0  10 px  0 ;
    }

    . seleccione {
      relleno : 5 px ;
      tamaño de fuente : 110 % ;
    }

    . estado {
      margen : 0 ;
      margen inferior : 20 px ;
      relleno : 10 px ;
      tamaño de fuente : 80 % ;
      antecedentes : # EEE ;
      borde : 1 px punteado # DDD ;
    }

    . estado - ERROR {
      color de fondo : rojo;
      color : blanco;
      tamaño de fuente : 120 % ;
    }

    . status - SUCCESS {
      color de fondo : verde;
      font-weight : negrita;
      color : blanco;
      tamaño de fuente : 120 %
    }

    . pequeño {
      tamaño de fuente : 0,7 rem ;
      font-weight : normal;
    }

    . versión {
      tamaño de fuente : 80 % ;
    }

    . form-field {
      borde : 1 px sólido # AAA ;
      relleno : 8 px ;
      ancho : 280 px ;
    }

    . info {
      margen superior : 0 ;
      tamaño de fuente : 80 % ;
      color : # 777 ;
    }

    . enviar {
      color de fondo : # 378de5 ;
      borde : 0 ;
      color : # ffffff ;
      tamaño de fuente : 15 px ;
      relleno : 10 px  24 px ;
      margen : 20 px  0  20 px  0 ;
      decoración de texto : ninguna;
    }

    . enviar : hover {
      color de fondo : # 2c6db2 ;
      cursor : puntero;
    }
    - >
  </ estilo >
</ cabeza >
< cuerpo >
< p  class = " status status-- <? php  echo  strtoupper ( key ( $ GLOBALS [ 'status' ])); ?> " >
  Estado: <? Php  echo  reset ( $ GLOBALS [ 'status' ]); ?> < Br />
  < span  class = " small " > Tiempo de procesamiento: <? php  echo  $ time ; ?> segundos </ span >
</ p >
< form  action = "" método = " POST " >
  <conjunto de campos >
    < h1 > Descomprimidor de archivos </ h1 >
    < label  for = " zipfile " > Seleccione el archivo .zip o .rar o el archivo .gz que desea extraer: </ label >
    < select  name = " zipfile " size = " 1 " class = " select " >
      <? php  foreach ( $ unzipper -> zipfiles  como  $ zip ) {
        echo  "<option> $ zip </option>" ;
      }
      ?>
    </ seleccionar >
    < label  for = " extpath " > Ruta de extracción (opcional): </ label >
    < input  type = " text " name = " extpath " class = " form-field " />
    < p  class = " info " > Ingrese la ruta de extracción sin barras al principio o al final (por ejemplo, "mypath"). Si se deja vacío, se utilizará el directorio actual. </ p >
    < input  type = " submit " name = " dounzip " class = " submit " value = " Descomprimir archivo " />
  </ fieldset >

  <conjunto de campos >
    < h1 > Cremallera de archivo </ h1 >
    < label  for = " zippath " > Ruta que debe estar comprimida (opcional): </ label >
    < input  type = " text " name = " zippath " class = " form-field " />
    < p  class = " info " > Ingrese la ruta a comprimir sin barras al principio o al final (por ejemplo, "zippath"). Si se deja vacío, se utilizará el directorio actual. </ p >
    < input  type = " submit " name = " dozip " class = " submit " value = " Archivo Zip " />
  </ fieldset >
</ formulario >
< p  class = " version " > Versión de descompresión: <? php  echo  VERSION ; ?> </ p >
</ cuerpo >
</ html >