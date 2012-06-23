<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="application-name" content="GUTO W" /> 
        <meta name="author" content="Augusto Weiand, guto.weiand@gmail.com" />
        <meta name="keywords" content="Protocolo de rede 2012" />

        <title>Protocolo de Rede 2012</title>

    </head>
    <body>
        <form name="dados" method="post" action="procProtocolo.php" enctype="multipart/form-data">
            <table width="100%">
                <tr>
                    <td>
                        <label for="endOrig">End. de Origem</label><br />
                        <input type="text" id="endOrig" name="endOrig" value="<?php echo $_SERVER['SERVER_ADDR'] ?>" />
                    </td>
                    <td>
                        <label for="endDest">End. de Destino</label><br />
                        <input type="text" id="endDest" name="endDest" value="<?php echo $_SERVER['SERVER_ADDR'] ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Arquivo</label><br />
                        <input type="file" name="arquivo[]" id="arquivo" multiple="multiple"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Tipo de Mensagem</label><br />
                        <select name="tipoMens" id="tipoMens">
                            <?php /*
                            <option value="<?php echo 0x00 ?>">NRM</option>
                            <option value="<?php echo 0x05 ?>">URG</option>
                            <option value="<?php echo 0x06 ?>">ACK</option>
                            <option value="<?php echo 0x0F ?>">PSH</option>
                            <option value="<?php echo 0x18 ?>">RST</option>
                            <option value="<?php echo 0x16 ?>">SYN</option>
                            <option value="<?php echo 0x17 ?>">FIN</option>
                            <option value="<?php echo 0x15 ?>">NACK</option>
                            */ ?>
                            <option value="0x00">NRM</option>
                            <option value="0x05">URG</option>
                            <option value="0x06">ACK</option>
                            <option value="0x0F">PSH</option>
                            <option value="0x18">RST</option>
                            <option value="0x16">SYN</option>
                            <option value="0x17">FIN</option>
                            <option value="0x15">NACK</option>                            
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>Ação</label><br />
                        Enviar? Receber?
                        <input type="checkbox" name="coddecod" id="coddecod" value="1" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" value="Enviar" />
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>