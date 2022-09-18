import React, { Component } from 'react';

export default class WidgetHead extends Component {
    render() {
        return (
            <>
                <div className={`flag-icon flag-icon-${this.props.baseCode.substring(0, 2).toLowerCase()}`}></div>&nbsp; {this.props.baseCode.substring(0, 2)} Dollar Exchange Rate
            </>
        )
    }
}