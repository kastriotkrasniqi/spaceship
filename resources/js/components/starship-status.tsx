
import React, { useEffect, useRef, useState } from "react";


export const StarshipStatus = ({  }) => {

    const webSocketChannel = `starship-tracking`;



    const connectWebSocket = () => {
        Echo.private(webSocketChannel)
            .listen('GotMessage', async (e) => {
                // e.message
                await getMessages();
            });
    }

    const getMessages = async () => {
        try {
            const m = await axios.get(`${rootUrl}/messages`);
        } catch (err) {
            console.log(err.message);
        }
    };

    useEffect(() => {
        getMessages();
        connectWebSocket();

        return () => {
            window.Echo.leave(webSocketChannel);
        }
    }, []);

    return (
        <div className="row justify-content-center">
            <div className="col-md-8">
                <div className="card">
                    <div className="card-header">Starship Status</div>
                    <div className="card-body" style={{height: "500px", overflowY: "auto"}}>

                    </div>
                    <div className="card-footer">
                    </div>
                </div>
            </div>
        </div>
    );
};
