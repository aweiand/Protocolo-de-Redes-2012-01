<?php

/*
 * Classe para protocolo de rede 2012/01
 */

class protocolo {

    function protocolo() {
        
    }

    /*
     * Funçao para converter o ip passado no parametro
     * se $empacota for verdadeiro ele transforma o IP
     * passado no parametro para hexadecimal e apos passa
     * para um inteiro para ser gravado no arquivo final
     */
    function convIP($ip, $empacota = true) {
        if ($empacota) {
            $ip2 = explode('.', $ip);
            $ipfim = chr($ip2[0]) . chr($ip2[1]) . chr($ip2[2]) . chr($ip2[3]);
            ;
        } else {
            /* $ipfim = ord(substr($ip,2,1)).".".ord(substr($ip,3,1)).".".
              ord(substr($ip,4,1)).".".ord(substr($ip,5,1)); */
        }
        return $ipfim;
    }
	
    /*
     * Funçao para converter o ip passado no parametro
     * se $origem for verdadeiro ele transforma o IP
     * passado no parametro para hexadecimal e apos passa
     * para um inteiro para ser gravado no arquivo final
     */
    function convIPVolta($ip, $origem = true) {
        if ($origem) {
            $ipfim = ord(substr($ip, 2, 1)) . "." . ord(substr($ip, 3, 1)) . "." .
                    ord(substr($ip, 4, 1)) . "." . ord(substr($ip, 5, 1));
        } else {
            $ipfim = ord(substr($ip, 6, 1)) . "." . ord(substr($ip, 7, 1)) . "." .
                    ord(substr($ip, 8, 1)) . "." . ord(substr($ip, 9, 1));
        }
        return $ipfim;
    }	

    /*
     * Aqui abro o arquivo passado no parâmetro e retorno uma
     * matriz com os dados do arquivo lido na posição $dados[][0]
     * e na posição $dados[[][1] o tamanho do dado contido no array 
     * esta função é somente para converter o arquivo para inserir o
     * protocolo e não para decodificar
     */
    function leDadoIn($arq) {
        //Aqui pego o local temporário do arquivo no servidor
        $arq_nome_temp = $arq['tmp_name'][0];
        //Abro ele somente para leitura
        $fp = fopen($arq_nome_temp, 'rb');

        //Zero a variável para controle da matriz e numero de pacotes
        $i = pacoteInicial;
        //Aqui leio pedaços de tamanhoArquivo e jogo eles no array $conteudo[$i]
        while (!feof($fp)) {
            $dados[$i][0] = fread($fp, tamanhoArquivo);
            $dados[$i][1] = strlen($dados[$i][0]);
            $i++;
        }
        //Fecho o arquivo
        fclose($fp);

        return $dados;
    }

	/*
	 * Função para gerar o checksum do cabeçalho 
	 */
    function chkCabecalho($endOrig, $endDest, $tamanho, $numSeq, $TipoMens) {
		$chkCab = (SOH ^ SYN ^ 
		ord($endOrig[0]) ^ ord($endOrig[1]) ^ ord($endOrig[2]) ^ ord($endOrig[3])
		^
		ord($endDest[0]) ^ ord($endDest[1]) ^ ord($endDest[2]) ^ ord($endDest[3])
		^ ord($tamanho[0]) ^ ord($tamanho[1])
		^ ($numSeq[0]) ^ ($numSeq[1])
		^ ($TipoMens));
		if (Ultradebug){
			echo "<br /><br />";
            echo "CheckCabecalho ---->> ".$chkCab." <<-----";
			echo '<br />IP ->';
			echo $endOrig;			
			echo '<br />IP ->';
			echo $endDest;			
			echo '<br />SOH ->';
			echo SOH;
			echo '<br />SYN ->';
			echo SYN;			
			echo '<br />Tamanho ->';
			echo $tamanho;
			echo '<br />NumSeq ->';
			echo $numSeq;
			echo '<br />TipoMens ->';
			echo $TipoMens;		
			echo "<br /><br />";			
		}
        // $chkCab = dechex($chkCab);
        return $chkCab;
    }

	/*
	 * Função para gerar o checksum do cabeçalho quando estamos voltando
	 */	
    function chkCabecalhoVolta($endOrig, $endDest, $tamanho, $synr, $sohr, $numSeq, $TipoMens) {
        $chkCab = ($synr ^ $sohr ^ 
		ord($endOrig[0]) ^ ord($endOrig[1]) ^ ord($endOrig[2]) ^ ord($endOrig[3])
		^ 
		ord($endDest[0]) ^ ord($endDest[1]) ^ ord($endDest[2]) ^ ord($endDest[3])
		^ ord($tamanho[0]) ^ ord($tamanho[1])
		^ ($numSeq[0]) ^ ($numSeq[1])
		^ ($TipoMens));
		if (Ultradebug){
			echo "<br /><br />";
            echo "ChkCabecalho ---->> ".$chkCab." <<-----";
			echo '<br />IP ->';
			echo $endOrig;			
			echo '<br />IP ->';
			echo $endDest;			
			echo '<br />SOH ->';
			echo $sohr;
			echo '<br />SYN ->';
			echo $synr;			
			echo '<br />Tamanho ->';
			echo $tamanho;
			echo '<br />NumSeq ->';
			echo $numSeq;
			echo '<br />TipoMens ->';
			echo $TipoMens;		
			echo "<br /><br />";			
		}
        // $chkCab = dechex($chkCab);
        return $chkCab;
    }

	/*
	 * Função para gerar os dois tipos de cheksum de ida e volta
	 */	
    function chkMens2($dados, $volta = false) {
        $hex_ary = 0;
        $total   = strlen($dados) -1;
        $i = 0;
        if (Ultradebug){
			echo "<br /><br />";
            echo "ChkMensagem ---------------->>>>>>";
			echo "<br /><br />";
		}
        foreach (str_split($dados) as $chr) {
            if ($volta)
                if ($i==$total)
                    break;
            // $hex_ary+= dechex(ord($chr));
			$hex_ary+= (ord($chr));
            $i++;
            if (Ultradebug){
				echo ord($chr)."  | DecHex ";
                echo dechex(ord($chr));
                echo "<br />";
            }
        }
        if (Ultradebug){
			echo "<br /><br />";
            echo "<<<<<<----------------";
			echo "<br /><br />";
		}
        // $chkMen = (substr($hex_ary, strlen($hex_ary) - 2, 2));
		$chkMen = $hex_ary;
        if (Ultradebug){
			echo "<br /><br />";
            echo "ChkMensagem ---->> ".$chkMen." <<-----";
			echo "<br /><br />";
		}
        return $chkMen;
    }

}

?>