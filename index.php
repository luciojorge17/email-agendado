<?php
/**
 * @author Lucio Jorge
 */

include_once 'conexao.php';
include_once 'envia_email.php';

/** Assunto do e-mail */
$assunto = "Relatório de títulos a receber";
/** Consulta SQL de todos os vendedores ativos */
$sql = "select cd_entidade, ds_entidade, ds_email from tbl_entidades where x_vendedor = 1 and x_ativo = 1";
$consulta = odbc_exec($conexao, $sql);

/** Consulta os títulos para cada vendedor cadastrado e ativo */
while ($vendedor = odbc_fetch_array($consulta)) {
  $idVendedor = $vendedor['cd_entidade'];
  $nomeVendedor = utf8_encode(ucwords(strtolower($vendedor['ds_entidade'])));
  /** E-mail do destinatário */
  $email = $vendedor['ds_email'];
  /** Cabeçalho do e-mail */
  $cabecalho = "Olá $nomeVendedor,<br>Segue abaixo lista dos títulos vencidos até a data de hoje.<br><br>";
  $tabela = "";
  /** Consulta SQL para listar os títulos */
  $sqlTitulos = "
    SELECT PARCELA.CD_LANCAMENTO, TITULO.NR_DOCUMENTO, PARCELA.NR_PARCELA, TITULO.CD_VENDEDOR, TITULO.DS_CLIENTE, ENTIDADE.DS_EMAIL, ENTIDADE.NR_DDD, ENTIDADE.NR_TELEFONE, PARCELA.DT_VENCIMENTO, DATEDIFF(DAY, PARCELA.DT_VENCIMENTO, GETDATE()) AS DIAS, PARCELA.VL_PARCELA, PARCELA.VL_SALDO
         FROM SEL_FINANCEIRO_TITULOS_ARECEBER_PARCELAS AS PARCELA
             INNER JOIN SEL_FINANCEIRO_TITULOS_ARECEBER AS TITULO ON PARCELA.CD_LANCAMENTO = TITULO.CD_LANCAMENTO
              INNER JOIN SEL_ENTIDADES AS ENTIDADE ON ENTIDADE.CD_ENTIDADE = TITULO.CD_CLIENTE
         WHERE PARCELA.VL_SALDO > 0 AND PARCELA.DT_VENCIMENTO < GETDATE() AND TITULO.CD_VENDEDOR=$idVendedor
         ORDER BY PARCELA.DT_VENCIMENTO";
  $consultaTitulos = odbc_exec($conexao, $sqlTitulos);
  if (odbc_num_rows($consultaTitulos) > 0) {
    /** Cabeçalho da tabela */
    $tabela .=
      '<table>
        <thead>
          <tr>
            <th>Lanç.</th>
            <th>Documento</th>
            <th>Nº Parcela</th>
            <th>Cliente</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Vencimento</th>
            <th>Dias em atraso</th>
            <th style="text-align: right">Valor Parcela</th>
            <th style="text-align: right">Saldo</th>
          </tr>
        </thead>
        <tbody>';

    /** Adiciona cada título à tabela */
    while ($titulo = odbc_fetch_array($consultaTitulos)) {
      /** Formata os dados do banco para apresentar de uma forma melhor para o vendedor */
      $lancamento = $titulo['CD_LANCAMENTO'];
      $documento = $titulo['NR_DOCUMENTO'];
      $parcela = $titulo['NR_PARCELA'];
      $telefone = "(" . $titulo['NR_DDD'] . ") " . $titulo['NR_TELEFONE'];
      $emailCliente = utf8_encode(strtolower($titulo['DS_EMAIL']));
      $nomeCliente = utf8_encode(ucwords(strtolower($titulo['DS_CLIENTE'])));
      $vencimento = date('d/m/Y', strtotime($titulo['DT_VENCIMENTO']));
      $dias = $titulo['DIAS'];
      $valorParcela = number_format($titulo['VL_PARCELA'], 2, ',', '.');
      $valorSaldo = number_format($titulo['VL_SALDO'], 2, ',', '.');
      $tabela .=
        '<tr>
          <td>' . $lancamento . '</td>
          <td>' . $documento . '</td>
          <td>' . $parcela . '</td>
          <td>' . $nomeCliente . '</td>
          <td>' . $telefone . '</td>
          <td>' . $emailCliente . '</td>
          <td>' . $vencimento . '</td>
          <td>' . $dias . '</td>
          <td style="text-align: right">' . $valorParcela . '</td>
          <td style="text-align: right">' . $valorSaldo . '</td>
        </tr>';
    }

    $tabela .=
      '
        </tbody>
      </table>';

    $mensagem = $cabecalho . $tabela;
    if (enviaEmail(1, $email, $nomeVendedor, $assunto, $mensagem)) {
      echo '[agendado] E-mail enviado para ' . $nomeVendedor . '<br>';
    } else {
      echo '[agendado] Falha no envio do e-mail para ' . $nomeVendedor . '<br>';
    }
  }
}
