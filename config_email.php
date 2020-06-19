<?php
function get_config($idEmpresa){
  $empresas = [];
  $empresas[1] = [
    "empresa" => "Nome da empresa",
    "email" => "luciojorge17@gmail.com",
    "senha" => "Camisa20",
    "porta" => 587,
    "smtp" => "smtp.gmail.com",
    "auth" => TRUE,
    "secure" => "tls"
  ];
  $empresas[2] = [
    "empresa" => "Nome da empresa",
    "email" => "email@email.com",
    "senha" => "senha",
    "porta" => 587,
    "smtp" => "smtp.gmail.com",
    "auth" => TRUE,
    "secure" => "ssl"
  ];
  return $empresas[$idEmpresa];
}