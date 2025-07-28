class TwitchRequest {
    constructor(clientId, clientSecret, extensionToken) {
        this.channelId = "";
        this.clientId = clientId;
        this.clientSecret = clientSecret;
        this.extensionToken = extensionToken; // Token spécifique pour l'extension
        this.query = {
            "getLastClip": async function (param, clientId, token, fnc) {
                if (!param.broadcaster) {
                    console.error("No Broadcaster param");
                    return false;
                }
                const channelUrl = `https://api.twitch.tv/helix/users?login=${param.broadcaster}`;
                const channelRequestOptions = {
                    method: "GET",
                    headers: {
                        "Client-Id": clientId,
                        "Authorization": `Bearer ${token}`
                    }
                };
                const channelResponse = await fetch(channelUrl, channelRequestOptions);
                if (!channelResponse.ok) {
                    throw new Error(`API Error: ${channelResponse.status} - ${channelResponse.statusText}`);
                }

                const channelResult = await channelResponse.json();
                if (channelResult.data && channelResult.data.length > 0) {
                    let broadcaster_id = channelResult.data[0].id;
                    const clipUrl = `https://api.twitch.tv/helix/clips?broadcaster_id=${broadcaster_id}&is_featured=true`;
                    const clipRequestOptions = {
                        method: "GET",
                        headers: {
                            "Client-Id": clientId,
                            "Authorization": `Bearer ${token}`
                        }
                    };
                    const clipResponse = await fetch(clipUrl, clipRequestOptions);
                    if (!clipResponse.ok) {
                        throw new Error(`API Error: ${clipResponse.status} - ${channelResponse.statusText}`);
                    }

                    const clipResult = await clipResponse.json();
                    if (clipResult.data && clipResult.data.length > 0) {
                        fnc.call(this,clipResult.data);
                    }
                }
            },
            "getChannelInfo": async function (param, clientId, token, fnc) {
                if (!param.broadcaster) {
                    console.error("No Broadcaster param");
                    return false;
                }

                const channelUrl = `https://api.twitch.tv/helix/users?login=${param.broadcaster}`;
                const channelRequestOptions = {
                    method: "GET",
                    headers: {
                        "Client-Id": clientId,
                        "Authorization": `Bearer ${token}`
                    }
                };

                try {
                    const channelResponse = await fetch(channelUrl, channelRequestOptions);
                    if (!channelResponse.ok) {
                        throw new Error(`API Error: ${channelResponse.status} - ${channelResponse.statusText}`);
                    }

                    const channelResult = await channelResponse.json();
                    if (channelResult.data && channelResult.data.length > 0) {
                        this.channelId = channelResult.data[0].id;
                        if (fnc != undefined)
                            fnc.call(this, channelResult.data[0]);
                    } else {
                        console.warn("No data found for broadcaster:", param.broadcaster);
                    }
                } catch (error) {
                    console.error("Error fetching channel info:", error);
                }
            }
        };
    }

    addQuery(queryName, fnc) {
        this.query[queryName] = fnc;
    }

    async getUserId(username) {
        const res = await fetch(`https://api.twitch.tv/helix/users?login=${username}`, {
            headers: {
                'Client-ID': this.clientId,
                'Authorization': 'Bearer ' + token
            }
        });
        const data = await res.json();
        console.dir(data.data);
        /*  let id = data.data[0]?.id;
          this.getFollowDate(id, token);*/
    }
    async getFollowDate(viewerId, token) {
        const res = await fetch(`https://api.twitch.tv/helix/users/follows?from_id=${viewerId}&to_id=${this.channelId}`, {
            headers: {
                'Client-ID': this.clientId,
                'Authorization': 'Bearer ' + token
            }
        });
        const data = await res.json();
        if (data.total > 0) {
            return new Date(data.data[0].followed_at);
        }
        return null;
    }
    async getApiToken(scopeParam) {
        const tokenUrl = "https://id.twitch.tv/oauth2/token";
        const scope = scopeParam ? `&scopes=${scopeParam}` : "";
        const tokenRequestOptions = {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `client_id=${this.clientId}&client_secret=${this.clientSecret}&grant_type=client_credentials${scope}`
        };

        try {
            const tokenResponse = await fetch(tokenUrl, tokenRequestOptions);
            if (!tokenResponse.ok) {
                throw new Error(`Token API Error: ${tokenResponse.status} - ${tokenResponse.statusText}`);
            }

            const tokenResult = await tokenResponse.json();
            return tokenResult.access_token;
        } catch (error) {
            console.error("Error fetching API token:", error);
            throw error;
        }
    }

    async validateToken(token) {
        const validateUrl = "https://id.twitch.tv/oauth2/validate";
        const validateOptions = {
            method: "GET",
            headers: {
                "Authorization": `OAuth ${token}`
            }
        };

        try {
            const validateResponse = await fetch(validateUrl, validateOptions);
            if (!validateResponse.ok) {
                throw new Error(`Token validation failed: ${validateResponse.status} - ${validateResponse.statusText}`);
            }

            const validationData = await validateResponse.json();
            return validationData.user_id; // Retourne l'ID utilisateur associé au token
        } catch (error) {
            console.error("Error validating token:", error);
            throw error;
        }
    }

    async execQuery(queryName, param, fnc, useExtensionToken = false, scopeParam) {
        try {
            const token = useExtensionToken
                ? this.extensionToken
                : await this.getApiToken(scopeParam);

            const userIdFromToken = await this.validateToken(token);

            // Vérification si `broadcaster_id` est dans les paramètres pour correspondre à l'utilisateur du token.
            if (param.broadcaster_id && param.broadcaster_id !== userIdFromToken) {
                throw new Error(
                    `broadcaster_id (${param.broadcaster_id}) does not match user_id from token (${userIdFromToken}).`
                );
            }

            await this.query[queryName](param, this.clientId, token, fnc);
        } catch (error) {
            console.error(`Error executing query '${queryName}':`, error);
        }
    }
}
