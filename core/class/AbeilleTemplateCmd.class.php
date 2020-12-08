<?php

class AbeilleTemplateCmd
{

    /**
     * Will retour the Template (uniqId) used by a Cmd for specific Abeille
     * @param           eqLogicalIdRef  logicalId of the Abeille
     * @param           logicalId       logicalId of the Cmd
     * 
     * @return          Return retour the Template (uniqId) used by an Abeille
     */
    public static function uniqIdUsedByAnAbeilleCmd( $eqLogicalIdRef, $logicalId ) {
        $eqLogicalId = Abeille::byLogicalId( $eqLogicalIdRef, 'Abeille', false )->getId();
        return AbeilleCmd::byEqLogicIdAndLogicalId( $eqLogicalId, $logicalId, false )->getConfiguration('uniqId', '-1');
    }

    /**
     * Will collect all Cmd with a specific template (uniqId)
     *
     * @return          Return all Cmd with a specific template (uniqId)
     */
    public static function getCmdByTemplateUniqId( $uniqId ) {
        $return = array();
        $allCmdWithUniqId = AbeilleCmd::searchConfiguration( 'uniqId' );

        foreach ( $allCmdWithUniqId as $key=>$cmdWithUniqId ) {
            if ( $cmdWithUniqId->getConfiguration('uniqId', '') == $uniqId ) {
                $return[] = $cmdWithUniqId;
            }
        }

        return $return;
    }

    /**
     * Will return the 'main' parameter for the device stored in the template
     * @param           uniqId Id du template que l selectionne
     * @param           param un des paramatres principaux comme: name, isVisible, order, type, subtype, invertBinary, template...
     *
     * @return          Return return the 'main' parameter for the device stored in the template
     */
    public static function getMainParamFromTemplate( $uniqId, $param ) {
        $jsonArray = AbeilleTemplateCommon::getJsonForUniqId( $uniqId );
        if ($jsonArray == -1) return -1;
        $keys = array_keys ( $jsonArray );
        if (count($keys)!=1) return 0;
        if (!isset($jsonArray[$keys[0]][$param])) return 0;
        return $jsonArray[$keys[0]][$param];
    }

    /**
    * Will return the configuration item for the device stored in the template
    * @param            uniqId of the template that we want to use
    * @param            item in the configuration that we want 
    *
    * @return          Return return the configuration item for the device stored in the template if exist otherwise ''
    */
    public static function getConfigurationFromTemplate( $uniqId, $item ) {
        $configurationArray = self::getMainParamFromTemplate( $uniqId, 'configuration' );
        
        if (isset($configurationArray[$item])) return $configurationArray[$item];
        else return '';
    }

        /**
    * Will compare cmd from Abeille to their template
    *
    * @return          Return no, will echo the result during execution.
    */
    public static function compareAllCmdWithTemplate() {
        $items = array( 'isVisible'=>'getIsVisible',
                        'name'=>'getName',
                        'isHistorized'=>'getIsHistorized', 
                        'Type'=>'getType', 
                        'subType'=>'getSubType',
                    );

            // 'order'=>'getOrder'
            // invertBinary setDisplay('invertBinary'
            // 'template'=>'getTemplate',

        // Take Abeile one by one and check if Template value is identical
        foreach ( Abeille::byType('Abeille') as $abeille ) {
            // Don't proceed with Ruche as specific template
            if (strpos($abeille->getLogicalId(), 'Ruche')>1) continue;

            echo "\nAbeille: ".$abeille->getName()."\n";
            foreach ( $abeille->getCmd() as $cmd ) {
                $uniqId = $cmd->getConfiguration( 'uniqId', -1 );
                if ( $uniqId == -1 ) {
                    echo "    ".$abeille->getCmd().": This cmd doesn t have a uniqId, I can t identify it s template !\n";
                    continue;
                }
                if (AbeilleTemplateCommon::getJsonFileNameForUniqId($uniqId)==-1) {
                    echo "    ".$cmd->getName().": This uniqId (".$uniqId."), doesn t correspond to any template !\n";
                    continue;
                }

                foreach ( $items as $item=>$fct ) {
                    $templateValue = AbeilleTemplateCmd::getMainParamFromTemplate($uniqId, $item);
                    if ( $templateValue == -1 ) {
                        echo "    ".$cmd->getName().": Error template not found for this parameter (".$uniqId."->".$item.") !\n";
                        continue;
                    }
                    if ($cmd->$fct() != $templateValue) {
                        echo "    ".$cmd->getName().": ".$cmd->$fct().' <-> '.$templateValue."\n";
                        // var_dump(AbeilleTemplateCommon::getJsonFileNameForUniqId( $uniqId ));
                    }
                }
            }
            
        }
    }

}


?>
