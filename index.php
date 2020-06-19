<?php
include_once 'conexao.php';
include_once 'envia_email.php';

$assunto = "Relatório de títulos a receber";
$sql = "select cd_entidade, ds_entidade, ds_email from tbl_entidades where x_vendedor = 1 and x_ativo = 1";
$consulta = odbc_exec($conexao, $sql);

while ($vendedor = odbc_fetch_array($consulta)) {
  $idVendedor = $vendedor['cd_entidade'];
  $nomeVendedor = utf8_encode(ucwords(strtolower($vendedor['ds_entidade'])));
  $email = $vendedor['ds_email'];
  $cabecalho = "Olá $nomeVendedor,<br>Segue abaixo lista dos títulos vencidos até a data de hoje.<br><br>";
  $tabela = "";
  $sqlTitulos = "
    select parcela.cd_lancamento, titulo.nr_documento, parcela.nr_parcela, nota.cd_vendedor, titulo.ds_cliente, nota.ds_email_cliente, nota.nr_ddd_cliente, nota.nr_telefone_cliente, parcela.dt_vencimento, DATEDIFF(DAY, parcela.dt_vencimento, GETDATE()) as dias, parcela.vl_parcela, parcela.  vl_saldo
	  from sel_financeiro_titulos_areceber_parcelas as parcela
	  	inner join sel_financeiro_titulos_areceber as titulo
	  		on parcela.cd_lancamento = titulo.cd_lancamento
	  	inner join sel_notas_faturamento as nota
	  		on titulo.cd_nota_faturamento = nota.cd_lancamento
	  where parcela.vl_saldo > 0 and parcela.dt_vencimento < GETDATE() and nota.cd_vendedor=$idVendedor
	  order by parcela.dt_vencimento";
  $consultaTitulos = odbc_exec($conexao, $sqlTitulos);
  if (odbc_num_rows($consultaTitulos) > 0) {
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

    while ($titulo = odbc_fetch_array($consultaTitulos)) {
      $lancamento = $titulo['cd_lancamento'];
      $documento = $titulo['nr_documento'];
      $parcela = $titulo['nr_parcela'];
      $telefone = "(" . $titulo['nr_ddd_cliente'] . ") " . $titulo['nr_telefone_cliente'];
      $emailCliente = utf8_encode(strtolower($titulo['ds_email_cliente']));
      $nomeCliente = utf8_encode(ucwords(strtolower($titulo['ds_cliente'])));
      $vencimento = date('d/m/Y', strtotime($titulo['dt_vencimento']));
      $dias = $titulo['dias'];
      $valorParcela = number_format($titulo['vl_parcela'], 2, ',', '.');
      $valorSaldo = number_format($titulo['vl_saldo'], 2, ',', '.');
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
