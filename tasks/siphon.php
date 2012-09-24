<?php

namespace Fuel\Tasks;

class Siphon 
{
	/**
	 * @var  array  array of valid relation types
	 */
	protected static $_valid_relations = array(
		'belongs_to'    => 'Orm\\BelongsTo',
		'has_one'       => 'Orm\\HasOne',
		'has_many'      => 'Orm\\HasMany',
		'many_many'     => 'Orm\\ManyMany',
	);
	
	public static function run()
	{
		\Cli::write(\Cli::color("Siphon: MySQL to Redis", 'green'));
		\Cli::write(\Cli::color("Date: ".date('Y-m-d H:i:s'), 'white'));
		\Cli::write('');
		
		\Config::load('siphon', true);
		
		$redis = \Redis::instance();
		
		$key_prefix = \Config::get('siphon.key_prefix', '');
		$key_separator = \Config::get('siphon.key_separator', '');
		$models = \Config::get('siphon.models', array());
		
		if(empty($models)) {
			\Cli::write(\Cli::color("No models specified in Siphon config!", 'red'));
		}
		
		foreach($models as $model => $data)
		{
			$model = 'Model_' . ucfirst(strtolower($model));
			
			$model_name = \Inflector::singularize($model::table());
			
			$properties = array_keys($model::properties());
			$relations = $model::relations();
			
			$rows = $model::find('all');
			
			$model_ids = array();
			
			foreach($rows as $row)
			{
				$model_params = array($key_prefix, $model::table());
				$model_key = implode($key_separator, $model_params);
				$redis->sadd($model_key, $row->id);
				
				$model_ids[] = $row->id;

				
				foreach($properties as $property)
				{
					if(isset($data['exclude'])) {
						if( in_array($property, $data['exclude'])) {
							continue;
						}
					}
					
					$params = array($key_prefix, $model_name, $row->id);
					$key = implode($key_separator, $params);
					
					$result = $redis->hset($key, $property, $row->$property);
					
					\Cli::write(sprintf("%s %s %s %s",
						'hset',
						$key,
						$property,
						escapeshellarg($row->$property)
					));
				}
			
				foreach($relations as $related_model => $related)
				{
					$type = get_class($related);
					$model_to = $related->name;
					$relationship_types = array_keys(static::$_valid_relations, $type);
					$relationship_type = $relationship_types[0];

					switch($relationship_type)
					{
						case 'many_many':
						case 'has_many':							
							$params = array($key_prefix, $model_name, $row->id, $related_model);
							$key = implode($key_separator, $params);
							$ids = array();
							
							foreach($row->$model_to as $link) {
								$redis->sadd($key, $link->id);
								$ids[] = $link->id;
								
							}
							
							\Cli::write(sprintf("sadd %s %s",
								$key,
								implode(' ', $ids)
							));

							break;
					}
				}
			}
			
			\Cli::write(sprintf("sadd %s %s", 
					$model_key, 
					implode(' ', $model_ids)));
		}
	}
	
	public static function clean()
	{
		\Config::load('siphon', true);
		$prefix = \Config::get('siphon.key_prefix', '');
		
		$redis = \Redis::instance();
		
		$keys = $redis->keys($prefix.'*');
		
		foreach($keys as $key) {
			$redis->del($key);
		}
	}
}
