<?php
	include "tfr-predicates.php";
	include "tfr-uri-constants.php";

	function write_triple($subject, $predicate, $object, $modifier="", $literal=True)
	{
		$spo = "";
		if($literal)
			$object = '"' . $object . '"' . $modifier;
		$spo = $spo . $subject . " " . $predicate . " " . $object . " ." . "\r\n"; 
		return $spo;
	}

	function extract_class($ntfile, $subject)
	{
		$class = write_triple($subject, CLASS_PREDICATE, FORMAT_CLASS_TYPE, "", false);		
		fwrite($ntfile, $class);
	}

   // pronom xml uri
   function make_xml_uri($identifier)
   {
      //e.g. http://www.nationalarchives.gov.uk/PRONOM/fmt/1.xml
      return "<http://www.nationalarchives.gov.uk/PRONOM/" . $identifier . ".xml>";       
   }

   // pronom standard uri
   function make_std_uri($identifier)
   {
      //e.g. http://www.nationalarchives.gov.uk/PRONOM/fmt/1
      return "<http://www.nationalarchives.gov.uk/PRONOM/" . $identifier . ">";
   }

	function extract_identifiers($ntfile, $subject, $xml)
	{
		$predicatevalue = "";

		$FormatIdentifier = $xml->FileFormatIdentifier;
		foreach($FormatIdentifier as $Identifier)
		{
			if (strcmp($Identifier->IdentifierType, 'MIME') == 0)
			{
				$predicatevalue = $predicatevalue . write_triple($subject, MEDIATYPE_PREDICATE, $Identifier->Identifier);
			}
			elseif (strcmp($Identifier->IdentifierType, 'PUID') == 0)
			{
				$predicatevalue = $predicatevalue . write_triple($subject, PUID_PREDICATE, $Identifier->Identifier);
            $predicatevalue = $predicatevalue . write_triple($subject, SAMEAS_PREDICATE, make_std_uri($Identifier->Identifier), "", false);
			}
		}

		fwrite($ntfile, $predicatevalue);

	}

	function extract_name_version($ntfile, $subject, $xml)
	{
		$name_version = "";
		$name_version = $name_version . write_triple($subject, NAME_PREDICATE, $xml->FormatName, "@en");
		if (strlen(trim($xml->FormatVersion)) > 0)
		{
			$name_version = $name_version . write_triple($subject, VERSION_PREDICATE, $xml->FormatVersion);
		}
		fwrite($ntfile, $name_version);
	}

	function extract_description($ntfile, $subject, $xml)
	{
		fwrite($ntfile, write_triple($subject, DESCRIPTION_PREDICATE, addslashes($xml->FormatDescription), "@en"));
	}

	function extract_type($ntfile, $subject, $xml)
	{
		$typetrip = "";

		$types = str_getcsv($xml->FormatTypes);
		foreach($types as $type)
		{
			if (strcmp(trim($type), "Audio") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, AUDIO_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Aggregate") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, AGGREGATE_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Database") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, DATABASE_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Dataset") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, DATASET_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Email") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, EMAIL_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "GIS") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, GIS_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Image (Raster)") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, RASTER_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Image (Vector)") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, VECTOR_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Page Description") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, PAGEDESC_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Presentation") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, PREZI_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Spreadsheet") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, SPREADSHEET_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Text (Mark-up)") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, MARKUP_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Text (Structured)") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, STRUCTUREDTXT_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Text (Unstructured)") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, UNSTRUCTUREDTXT_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Video") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, VIDEO_CLASS_TYPE, "", false);
			}
			elseif (strcmp(trim($type), "Word Processor") == 0)
			{
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, WPTXT_CLASS_TYPE, "", false);
			}
         elseif (strcmp(trim($type), "Model") == 0)
         {
				$typetrip = $typetrip . write_triple($subject, TYPE_PREDICATE, MODEL_CLASS_TYPE, "", false);
         }
         elseif (strcmp(trim($type), "") == 0)
         {
            //do nothing for empty type, XML returns ""
         }
         else
         {
            error_log("Unknown type in PRONOM data: " . trim($type));
         }
		}

		fwrite($ntfile, $typetrip);
	}

	function extract_extension($ntfile, $subject, $xml)
	{	
		$exttxt = "";
	
		foreach($xml->ExternalSignature as $ext)
		{
			if(strcmp($ext->SignatureType, "File extension") == 0)
			{			
				$exttxt = $exttxt . write_triple($subject, EXTENSION_PREDICATE, $ext->Signature);
			}
		}

		fwrite($ntfile, $exttxt);
	}

	function extract_alias($ntfile, $subject, $xml)
	{
		$aliastxt = "";

		$aliases = str_getcsv($xml->FormatAliases);
		foreach($aliases as $alias)
		{
			if(strlen(trim($alias)) > 0)
			{
				$aliastxt = $aliastxt . write_triple($subject, ALIAS_PREDICATE, $alias);
			}
		}

		fwrite($ntfile, $aliastxt); 
	}	

	function triple_mapper($ntfile, $subject, $formatXML)
	{
		extract_class($ntfile, $subject);
		extract_identifiers($ntfile, $subject, $formatXML);
		extract_name_version($ntfile, $subject, $formatXML);
		extract_alias($ntfile, $subject, $formatXML);		
		extract_description($ntfile, $subject, $formatXML);
		extract_type($ntfile, $subject, $formatXML);
		extract_extension($ntfile, $subject, $formatXML);
	}

	function mint_subject()
	{
		static $no = 1;
		$subject = "<" . BASEURI_SUBJECT . $no . ">";
		$no++;
		return $subject;
	}

	function pronom_to_rdf_map($ntfile, $data, $puid)
	{ 
		$xml = simplexml_load_string($data);
  
      if (substr($data, 0, 1) == '')
      {    
         error_log("Problem with file " . $puid . " may be empty.");
      }
      else
      {
		   $formatXML = $xml->report_format_detail->FileFormat;
         triple_mapper($ntfile, mint_subject(), $formatXML);
      }
	}
?>
