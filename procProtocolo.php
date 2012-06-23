<?php
include_once 'proto.class.php';
$prt = new protocolo();
/*
 * Aqui faço uma resenha para o super protocolo de rede, onde
 *  eu recebo um arquivo genérico e guardo ele em um arquivo 
 *  de texto
 */

// -----------------------------------------------
// Aqui defino os valores default do sistema
define('debug', true);
define('Ultradebug', false);
define('debugArq', false);
define('debugTam', false);
define('debugSaida', false);
define('pacoteInicial', 1);
define('tamanhoArquivo', 65535);
define('SOH', 0x01);
define('SYN', 0x16);
define('STX', 0x02);
define('ETX', 0x03);
define('EOT', 0x04);
// ------------------------------------------------
// Aqui recebo as variáveis vindas do form anterior
$endOrig = $_POST['endOrig'];
$endDest = $_POST['endDest'];
$arquivo = $_FILES['arquivo'];
$tipoMens = $_POST['tipoMens'];
// ------------------------------------------------
//Aqui verifico o que devo fazer com os dados vindos
if (!isset($_POST['coddecod'])) {
    if (debug)
        echo "<b>Você esta tentando criar os pacotes do protocolo</b><br /><br />";
// Aqui inicio a conversão dos ips para bytes
    if (debug)
        echo "<b>Listando Endereços</b><br />";
    if (debug)
        echo "Endereço de Origem ->" . $endOrig . "<br />";

    $ipOrg = $prt->convIP($endOrig, true);

    if (debug)
        echo "End. de Origem Convertido ->" . $ipOrg . "<br /><br />";
    if (debug)
        echo "Endereço de Destino ->" . $endDest . "<br />";

    $ipDest = $prt->convIP($endDest, true);

    if (debug)
        echo "Endereço de Destino Convertido ->" . $ipDest . "<br /><br />";
    if (debug)
        echo "--------------------------###--------------------------<br /><br />";
// ------------------------------------------------
// Aqui inicio a verificação de número de pacotes necessários
//  para enviar o arquivo
    if (debug)
        echo "<b>Listando Dados do Arquivo</b><br />";
    $dado = $prt->leDadoIn($arquivo);

    if (debugArq) {
        echo "<pre>";
        for ($ai = pacoteInicial; $ai < count($dado); $ai++) {
            echo "Conteúdo do Arquivo -> <br />" . var_dump($dado[$ai]) . "<br /><br />";
            echo "Tamanho -> " . $dado[$ai][1];
        }
        echo "</pre>";
    }

    if (debug)
        echo "--------------------------###--------------------------<br /><br />";
// -------------------------------------------------
//Teste de saída 
    if (debugSaida) {
        for ($a = pacoteInicial; $a < count($dado); $a++) {
            @$arq.= $dado[$a][0];
        }
        $filename = 'protocolo_' . $a . '_debug.txt';
        $handle = fopen($filename, 'w');
        fwrite($handle, $arq);
        echo "<br >Sucesso: Escrito o conteudo no arquivo ($filename)<br />";
        fclose($handle);
    }

//--------------------------------------------------
//Início do Cálculo dos checksums e gravação do arquivo
    for ($a = pacoteInicial; $a < count($dado)+pacoteInicial; $a++) {
        //Inicio a variavel que receberá os dados para escrever o arquivo
        $file = '';
        //Escrevo nele o cabeçalho do protocolo e o byte de sincronia
        $file.= chr(SOH) . chr(SYN);
        //Escrevo os dados do endereço de origem e destino
        $file.= ($ipOrg) . ($ipDest);
        //Escrevo o tamanho da mensagem
		 $file.= chr($dado[$a][1] >> 8).chr($dado[$a][1]);
        //Escrevo o numero de sequencia e o tipo de mensagem
        $file.= chr($a >> 8).chr($a) . chr($tipoMens);
        //Escrevo o Checksum do Cabeçalho
		//$chkCab = $prt->chkCabecalho($ipOrg, $ipDest, $dado[$a][1]);
		$chkCab = $prt->chkCabecalho($ipOrg, $ipDest, $dado[$a][1], chr($a >> 8).chr($a) , $tipoMens);
        $file.= chr($chkCab);
        if (debug)
            echo "<br />CheckSum do Cabeçalho ---> " . $chkCab . "<br />";
        //Escrevo o inicio da mensagem
        $file.= chr(STX);
        //Escrevo a mensagem
        $file.= $dado[$a][0];
        //Escrevo a finalização da mensagem e o final da transmissão
        $file.= chr(ETX) . chr(EOT);
        //Escrevo o checksum da mensagem
        $chkMen = $prt->chkMens2($file);
        $file.= chr($chkMen);
        if (debug)
            echo "<br />CheckSum da Mensagem ---> " . $chkMen . "<br />";
        //Defino um nome para o arquivo
        $filename = 'protocolo_' . $a . '.txt';
        //Crio o arquivo e abro ele para escrita
        $abreArq = fopen($filename, 'w');
        //Escrevo no arquivo aberto o conteudo
        fwrite($abreArq, $file);
        //Mensagem na tela para acompanhar o andamento
        echo "<br >Sucesso: Escrito o conteudo no arquivo ($filename)<br />";
        //Fecho o arquivo
        fclose($abreArq);
    }
} else {
    //Aqui inicio a decodificação e remonto o arquivo original
    if (debug)
        echo "<b>Você esta tentando re-criar os pacotes com informações vindas de arquivo(s)</b><br /><br />";
    //Aqui abro o(s) arquivo(s) vindos e coloco seus valores em um matriz
    // mas antes, verifico quantos aquivos vieram
    echo "<pre>";

    $i = count($arquivo['name']);
    $dado = '';
	//Aqui inicio um for para percorrer todos os arquivos recebidos
    for ($pos = 0; $pos < $i; $pos++) {
        $fp = fopen($arquivo['tmp_name'][$pos], 'rb');
		//Apos ter aberto o arquivo coloco ele na variavel para tratar apos
        $texto = fread($fp, filesize($arquivo['tmp_name'][$pos]));
        fclose($fp);
        if (debugArq){
            echo "<pre>";
            echo "Conteudo do Pacote ->";
            var_dump($texto);
            echo "<br /><pre>";
        }
		//Vejo quantos caracteres vieram
        $tam = strlen($texto);
		//coloco na variavel para montar novamente o arquivo, ja retirando o protocolo
        for ($j = 17; $j < $tam - 3; ++$j) {
            $dado.=$texto[$j];
        }

		//Inicio a leitura das informações do protocolo
        $sohr = ord(substr($texto, 0, 1));
        if (debug)
            echo "<br />SOH ->" . $sohr;

        $synr = ord(substr($texto, 1, 1));
        if (debug)
            echo "<br />SYN ->" . $synr;

        $endOrig = $prt->convIPVolta($texto, true);
        if (debug)
            echo "<br />EndOrg ->" . $endOrig;

        $endDest = $prt->convIPVolta($texto, false);
        if (debug)
            echo "<br />EndDest ->" . $endDest;

        $tamMens = (ord(substr($texto, 10, 1)) << 8) | (ord(substr($texto, 11, 1)));
        if (debug)
            echo '<br />TamMens ->' . $tamMens;

        $numSeq = (ord(substr($texto, 12, 1)) << 8) | (ord(substr($texto, 13, 1)));
        if (debug)
            echo '<br />NumSeq  ->' . $numSeq;

        $tipoMens = ord(substr($texto, 14, 1));
        if (debug)
            echo '<br />TipoMens ->' . $tipoMens;

        $chkCab = ord(substr($texto, 15, 1));
        if (debug)
            echo '<br />ChkCab   ->' . $chkCab;

        $stxr = ord(substr($texto, 16, 1));
        if (debug)
            echo '<br />STX   ->' . $stxr;

        $tam = strlen($texto);
        $etxr = ord(substr($texto, ($tam - 3), 1));
        if (debug)
            echo '<br />ETX   ->' . $etxr;

        $eotr = ord(substr($texto, ($tam - 2), 1));
        if (debug)
            echo '<br />EOT   ->' . $eotr;

        $chkMen = ord(substr($texto, ($tam - 1), 1));
        if (debug)
            echo '<br />Checksum  Mensagem ->' . $chkMen;
        echo '<br /><br />';
		
		//Inicio a checagem dos checksuns
        // if ($prt->chkCabecalhoVolta($prt->convIP($endOrig), $prt->convIP($endDest), $tamMens, $synr, $sohr) != $chkCab) {
			if ($prt->chkCabecalhoVolta($prt->convIP($endOrig), $prt->convIP($endDest), $tamMens, $synr, $sohr, $numSeq, $tipoMens) != $chkCab) {
            echo "<br /><b>CheckSum do Cabeçalho Errado!!!</b><br />";
        }
        else
            echo "<br /><b>CheckSum do Cabeçalho CORRETO!!!</b><br />";
		
		if (chr($prt->chkMens2($texto, true))!=chr($chkMen)){
            echo "<br /><b>CheckSum da Mensagem Errado!!!</b><br />";
        } else
            echo "<br /><b>CheckSum da Mensagem CORRETO!!!</b><br />";
    }
	//Abro um novo arquivo para gravar os dados, gravo e libero o arquivo gerado
    $fp = fopen('ResultadoMensagemProtocolo.txt', 'w');
    fwrite($fp, $dado);
    fclose($fp);
}
?>
