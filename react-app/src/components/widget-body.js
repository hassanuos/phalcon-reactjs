import React, { Component } from 'react';

export default class WidgetHead extends Component {

    render() {
        return (
            <>
                <table className={`table mt-2`}>
                    <thead>
                    <tr><td>{`Cache Expire In ${this.props.expiryTime} seconds`}</td><th className={`text-right`}>1 {this.props.baseCode} = </th></tr>
                    </thead>
                    <tbody>
                    {this.props.countries !== null && this.props.countries.map(function (val, index) {
                        return <tr key={index}>
                            <td><div className={`flag-icon ${val.flag_class}`}></div>&nbsp; {val.country_name}</td>
                            <td className="text-right">{val.amount}</td>
                        </tr>
                    })}
                    <tr>
                        <td colSpan="2" className={`text-right`}>Rates {this.props.currentDay}</td>
                    </tr>
                    </tbody>
                </table>
            </>
        )
    }
}