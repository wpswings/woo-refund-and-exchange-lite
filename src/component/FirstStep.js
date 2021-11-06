import React,{useContext} from 'react';
import { TextField, FormControl, Switch, FormControlLabel, TextareaAutosize } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import Context from '../store/store';
import { __ } from '@wordpress/i18n';
const useStyles = makeStyles({
    margin: {
      marginBottom: '20px',
    },
});
const FirstStep = (props) => {
    const classes = useStyles();
    const ctx = useContext(Context);
    return(
        <FormControl component="fieldset" fullWidth className="fieldsetWrapper">
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedRefund']}
                onChange={ctx.changeHandler}
                name="checkedRefund"
                color="primary"
            />
            }
            label={ __( 'Enable Refund', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedOrderMsg']}
                onChange={ctx.changeHandler}
                name="checkedOrderMsg"
                color="primary"
            />
            }
            label={ __( 'Enable Order Message', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedOrderMsgEmail']}
                onChange={ctx.changeHandler}
                name="checkedOrderMsgEmail"
                color="primary"
            />
            }
            label={ __( 'Enable Order Message Related Email', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedExchange']}
                onChange={ctx.changeHandler}
                name="checkedExchange"
                color="primary"
            />
            }
            label={ __( 'Enable Exchange', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedCancel']}
                onChange={ctx.changeHandler}
                name="checkedCancel"
                color="primary"
            />
            }
            label={ __( 'Enable Cancel Order', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedCancelProd']}
                onChange={ctx.changeHandler}
                name="checkedCancelProd"
                color="primary"
            />
            }
            label={ __( 'Enable Cancel Order\'s Product', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedWallet']}
                onChange={ctx.changeHandler}
                name="checkedWallet"
                color="primary"
            />
            }
            label={ __( 'Enable Wallet', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
        </FormControl>
    )
}
export default FirstStep;