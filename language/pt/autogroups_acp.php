<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
* @Traduzido por: Leinad4Mind - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=610725
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_AUTOGROUPS_MANAGE'			=> 'Gerir Autogrupos',
	'ACP_AUTOGROUPS_MANAGE_EXPLAIN'	=> 'Ao usares este formulário, poderás adicionar, editar, visualizar e excluir configurações de Autogrupos.',
	'ACP_AUTOGROUPS_ADD'			=> 'Adicionar Autogrupo',
	'ACP_AUTOGROUPS_EDIT'			=> 'Editar Autogrupo',

	'ACP_AUTOGROUPS_GROUP_NAME'				=> 'Grupo',
	'ACP_AUTOGROUPS_GROUP_NAME_EXPLAIN'		=> 'Escolhe um grupo para automaticamente adicionar/remover utilizadores do mesmo.',
	'ACP_AUTOGROUPS_CONDITION_NAME'			=> 'Tipo de Autogrupo',
	'ACP_AUTOGROUPS_CONDITION_NAME_EXPLAIN'	=> 'Escolhe o tipo de condição que fará cada utilizador ser adicionado ou removido do grupo.',
	'ACP_AUTOGROUPS_MIN_VALUE'				=> 'Valor mínimo',
	'ACP_AUTOGROUPS_MIN_VALUE_EXPLAIN'		=> 'Os utilizadores serão adicionados para este grupo, se excederam o valor mínimo.',
	'ACP_AUTOGROUPS_MAX_VALUE'				=> 'Valor máximo',
	'ACP_AUTOGROUPS_MAX_VALUE_EXPLAIN'		=> 'Os utilizadores serão removidos deste grupo, se excederam o valor máximo. Deixa o campo vazio, se não desejares que os utilizadores sejam removidos do grupo.',
	'ACP_AUTOGROUPS_DEFAULT'				=> 'Configura o grupo por defeito',
	'ACP_AUTOGROUPS_DEFAULT_EXPLAIN'		=> 'Transforma o grupo no novo grupo por defeito do utilizador.',
	'ACP_AUTOGROUPS_NOTIFY'					=> 'Notificar o utilizador',
	'ACP_AUTOGROUPS_NOTIFY_EXPLAIN'			=> 'Enviar uma notificação para utilizadores depois que foram automaticamente adicionados ou removidos deste grupo.',

	'ACP_AUTOGROUPS_EXEMPT_GROUP'			=> 'Configura as excepções para o grupo por defeito',
	'ACP_AUTOGROUPS_EXEMPT_GROUP_EXPLAIN'	=> 'O grupo por defeito do utilizador não será automaticamente alterado se estiver seleccionado nesta lista. Selecciona diversos grupos ao premir <samp>CTRL</samp> (ou <samp>&#8984;CMD</samp> no Mac) e clicar sobre o grupo.',

	'ACP_AUTOGROUPS_CREATE_RULE'	=> 'Criar novo Autogrupo',
	'ACP_AUTOGROUPS_SUBMIT_SUCCESS'	=> 'Autogrupo configurado com sucesso.',
	'ACP_AUTOGROUPS_DELETE_CONFIRM'	=> 'Desejas excluir a configuração deste Autogrupo?',
	'ACP_AUTOGROUPS_DELETE_SUCCESS'	=> 'Autogrupo excluído com sucesso.',
	'ACP_AUTOGROUPS_EMPTY'			=> 'Não existem Autogrupos.',
	'ACP_AUTOGROUPS_NO_GROUPS'		=> 'Não existem grupos disponíveis',
	'ACP_AUTOGROUPS_INVALID_GROUPS'	=> 'Ocorreu um erro. Não foi seleccionado um grupo de utilizadores válido. <br /> Os autogrupos apenas pode ser usado com grupos definidos de utilizadores, que podem ser criados na página "Configurar grupos".',
	'ACP_AUTOGROUPS_INVALID_RANGE'	=> 'Ocorreu um erro. O valor mínimo e máximo não podem ser iguais.',

	// Conditions
	'AUTOGROUPS_TYPE_BIRTHDAYS'		=> 'Idade do utilizador',
	'AUTOGROUPS_TYPE_MEMBERSHIP'	=> 'Dias que o utilizador é membro',
	'AUTOGROUPS_TYPE_POSTS'			=> 'Mensagens',
	'AUTOGROUPS_TYPE_WARNINGS'		=> 'Avisos',
));
